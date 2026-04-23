<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountTransfer;
use App\Models\BankAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\JournalEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountTransferController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'from_bank_account_id' => 'required|exists:bank_accounts,id',
            'to_bank_account_id'   => 'required|exists:bank_accounts,id|different:from_bank_account_id',
            'amount'               => 'required|numeric|min:0.01',
            'to_amount'            => 'nullable|numeric|min:0.01',
            'exchange_rate'        => 'nullable|numeric|min:0.000001',
            'transfer_date'        => 'required|date',
            'description'          => 'nullable|string|max:255',
        ]);

        $from = BankAccount::findOrFail($data['from_bank_account_id']);
        $to   = BankAccount::findOrFail($data['to_bank_account_id']);

        if ($from->current_balance < $data['amount']) {
            return back()->with('error', "Insufficient balance in {$from->name}. Available: {$from->currency} " . number_format($from->current_balance, 0));
        }

        // Determine destination amount
        $isFx     = $from->currency !== $to->currency;
        $toAmount = $isFx && !empty($data['to_amount'])
            ? (float) $data['to_amount']
            : (float) $data['amount'];

        $exchangeRate = $isFx && !empty($data['exchange_rate'])
            ? (float) $data['exchange_rate']
            : null;

        DB::transaction(function () use ($data, $from, $to, $toAmount, $exchangeRate, $isFx) {
            $transfer = AccountTransfer::create([
                'from_bank_account_id' => $data['from_bank_account_id'],
                'to_bank_account_id'   => $data['to_bank_account_id'],
                'amount'               => $data['amount'],
                'to_amount'            => $toAmount,
                'exchange_rate'        => $exchangeRate,
                'from_currency'        => $from->currency,
                'to_currency'          => $to->currency,
                'transfer_date'        => $data['transfer_date'],
                'description'          => $data['description'] ?? null,
                'created_by'           => auth()->id(),
            ]);

            // Post journal entry
            $je = app(JournalEntryService::class)->onAccountTransfer($transfer);
            if ($je) {
                $transfer->update(['journal_entry_id' => $je->id]);
            }

            // Update balances — source loses `amount`, destination gains `to_amount`
            BankAccount::where('id', $transfer->from_bank_account_id)
                       ->decrement('current_balance', (float) $transfer->amount);
            BankAccount::where('id', $transfer->to_bank_account_id)
                       ->increment('current_balance', (float) $transfer->to_amount);
        });

        $msg = 'Transfer completed successfully.';
        if ($isFx && $exchangeRate) {
            $msg = "Transfer completed. Rate: 1 {$from->currency} = " . number_format($exchangeRate, 4) . " {$to->currency}. "
                 . "{$from->currency} " . number_format($data['amount'], 2) . " → {$to->currency} " . number_format($toAmount, 2);
        }

        return redirect()->route('admin.accounting.bank-accounts.index')->with('success', $msg);
    }

    public function reverse(AccountTransfer $accountTransfer)
    {
        abort_if($accountTransfer->isReversed(), 409, 'This transfer has already been reversed.');

        $transfer = $accountTransfer;
        $from = BankAccount::with('account')->findOrFail($transfer->from_bank_account_id);
        $to   = BankAccount::with('account')->findOrFail($transfer->to_bank_account_id);

        $fromAmt = (float) $transfer->amount;
        $toAmt   = $transfer->to_amount ? (float) $transfer->to_amount : $fromAmt;
        $isFx    = $transfer->isFxTransfer();

        // Guard: destination must still have enough balance to reverse
        if ($to->current_balance < $toAmt) {
            return back()->with('error',
                "Cannot reverse: {$to->name} only has {$to->currency} " . number_format($to->current_balance, 2) . " but reversal needs {$to->currency} " . number_format($toAmt, 2) . ".");
        }

        DB::transaction(function () use ($transfer, $from, $to, $fromAmt, $toAmt, $isFx) {
            // 1. Restore balances — undo the original transfer
            BankAccount::where('id', $from->id)->increment('current_balance', $fromAmt);
            BankAccount::where('id', $to->id)->decrement('current_balance', $toAmt);

            // 2. Determine TZS amounts for the reversal JE (functional currency)
            //    Must mirror the same logic used in JournalEntryService::onAccountTransfer
            if ($isFx) {
                $tzsAmt = ($transfer->from_currency === 'TZS') ? $fromAmt : $toAmt;
            } else {
                $tzsAmt = $fromAmt;
            }

            // 3. Create a reversal JE (swap Dr/Cr of the original, using TZS amounts)
            $rateNote = $isFx && $transfer->exchange_rate
                ? " (Rate: " . number_format((float) $transfer->exchange_rate, 4) . ")"
                : '';
            $jeDesc = "Reversal of {$transfer->reference}: {$from->name} ← {$to->name}{$rateNote}";

            $reversalLines = [];
            if ($from->account) {
                $reversalLines[] = [
                    'account_id'  => $from->account->id,
                    'description' => "Reversal — returned to {$from->name}" . ($isFx ? " ({$transfer->from_currency} " . number_format($fromAmt, 2) . ")" : ''),
                    'debit'       => 0,
                    'credit'      => $tzsAmt,
                ];
            }
            if ($to->account) {
                $reversalLines[] = [
                    'account_id'  => $to->account->id,
                    'description' => "Reversal — reversed from {$to->name}" . ($isFx ? " ({$transfer->to_currency} " . number_format($toAmt, 2) . ")" : ''),
                    'debit'       => $tzsAmt,
                    'credit'      => 0,
                ];
            }

            $reversalJe = null;
            if (!empty($reversalLines)) {
                $reversalJe = JournalEntry::create([
                    'entry_date'  => now()->toDateString(),
                    'description' => $jeDesc,
                    'reference'   => 'REV-' . $transfer->reference,
                    'source_type' => 'account_transfer_reversal',
                    'notes'       => "Reversed by " . auth()->user()->name,
                    'status'      => 'posted',
                    'created_by'  => auth()->id(),
                ]);
                foreach ($reversalLines as $line) {
                    $reversalJe->lines()->create($line);
                }

                // Mark the original JE as reversed
                if ($transfer->journal_entry_id) {
                    JournalEntry::where('id', $transfer->journal_entry_id)->update([
                        'is_reversed'    => true,
                        'reversed_by_id' => $reversalJe->id,
                    ]);
                }
            }

            // 3. Create a reversal AccountTransfer record
            $reversalTransfer = AccountTransfer::create([
                'from_bank_account_id'   => $transfer->from_bank_account_id,
                'to_bank_account_id'     => $transfer->to_bank_account_id,
                'amount'                 => $fromAmt,
                'to_amount'              => $toAmt,
                'exchange_rate'          => $transfer->exchange_rate,
                'from_currency'          => $transfer->from_currency,
                'to_currency'            => $transfer->to_currency,
                'transfer_date'          => now()->toDateString(),
                'description'            => "Reversal of {$transfer->reference}",
                'journal_entry_id'       => $reversalJe?->id,
                'created_by'             => auth()->id(),
                'reversal_of_transfer_id' => $transfer->id,
            ]);

            // 4. Mark original as reversed
            $transfer->update([
                'reversed_at' => now(),
                'reversed_by' => auth()->id(),
            ]);
        });

        return back()->with('success', "Transfer {$transfer->reference} has been reversed. Balances restored.");
    }
}
