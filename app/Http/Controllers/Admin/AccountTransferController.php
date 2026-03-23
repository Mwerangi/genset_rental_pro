<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountTransfer;
use App\Models\BankAccount;
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
            'amount'               => 'required|numeric|min:1',
            'transfer_date'        => 'required|date',
            'description'          => 'nullable|string|max:255',
        ]);

        $from = BankAccount::findOrFail($data['from_bank_account_id']);

        if ($from->current_balance < $data['amount']) {
            return back()->with('error', "Insufficient balance in {$from->name}. Available: {$from->currency} " . number_format($from->current_balance, 0));
        }

        DB::transaction(function () use ($data) {
            $transfer = AccountTransfer::create([
                'from_bank_account_id' => $data['from_bank_account_id'],
                'to_bank_account_id'   => $data['to_bank_account_id'],
                'amount'               => $data['amount'],
                'transfer_date'        => $data['transfer_date'],
                'description'          => $data['description'] ?? null,
                'created_by'           => auth()->id(),
            ]);

            // Post journal entry
            $je = app(JournalEntryService::class)->onAccountTransfer($transfer);
            if ($je) {
                $transfer->update(['journal_entry_id' => $je->id]);
            }

            // Update balances
            BankAccount::where('id', $transfer->from_bank_account_id)
                       ->decrement('current_balance', $transfer->amount);
            BankAccount::where('id', $transfer->to_bank_account_id)
                       ->increment('current_balance', $transfer->amount);
        });

        return redirect()->route('admin.accounting.bank-accounts.index')
                         ->with('success', 'Transfer completed successfully.');
    }
}
