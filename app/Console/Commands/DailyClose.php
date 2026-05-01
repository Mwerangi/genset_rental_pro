<?php

namespace App\Console\Commands;

use App\Models\BankAccount;
use App\Models\CashRequest;
use App\Models\DailyClosing;
use App\Models\Expense;
use App\Models\InvoicePayment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DailyClose extends Command
{
    protected $signature = 'accounting:daily-close
                            {--date= : Date to close (YYYY-MM-DD). Defaults to today.}
                            {--force : Re-run even if a closing already exists for this date.}
                            {--manual : Mark the closing as manually triggered (not scheduled).}';

    protected $description = 'Snapshot all account balances and transactions for the day and save to daily_closings.';

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))->toDateString()
            : now()->toDateString();

        $force  = $this->option('force');
        $isAuto = !$this->option('manual');

        $bankAccounts = BankAccount::with('account')
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($bankAccounts->isEmpty()) {
            $this->warn('No active bank accounts found.');
            return self::SUCCESS;
        }

        $this->info("Running daily close for date: {$date}");
        $bar = $this->output->createProgressBar($bankAccounts->count());
        $bar->start();

        $skipped = 0;
        $saved   = 0;

        foreach ($bankAccounts as $ba) {
            // Skip if already closed unless --force
            $existing = DailyClosing::forAccountDate($ba->id, $date);
            if ($existing && !$force) {
                $skipped++;
                $bar->advance();
                continue;
            }

            // ── Opening balance from posted JE lines before this date ─────
            $openingBalance = 0.0;
            if ($ba->account_id) {
                $before = DB::table('journal_entry_lines')
                    ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->where('journal_entries.status', 'posted')
                    ->where('journal_entry_lines.account_id', $ba->account_id)
                    ->where('journal_entries.entry_date', '<', $date)
                    ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
                    ->first();
                $openingBalance = round((float) $before->total_debit - (float) $before->total_credit, 2);
            } else {
                // Fallback: use current_balance adjusted back for today's movements
                // (computed after fetching today's totals below)
            }

            // ── Inflows ───────────────────────────────────────────────────
            $payments = InvoicePayment::with(['invoice.client', 'recordedBy'])
                ->where('bank_account_id', $ba->id)
                ->whereDate('payment_date', $date)
                ->where('is_reversed', false)
                ->orderBy('payment_date')
                ->get();

            // ── Outflows ──────────────────────────────────────────────────
            $expenses = Expense::with(['category', 'createdBy'])
                ->where('bank_account_id', $ba->id)
                ->where('status', 'posted')
                ->whereDate('expense_date', $date)
                ->orderBy('expense_date')
                ->get();

            $cashReqs = CashRequest::with(['requestedBy'])
                ->where('bank_account_id', $ba->id)
                ->whereNotNull('paid_at')
                ->whereDate('paid_at', $date)
                ->orderBy('paid_at')
                ->get();

            $totalIn  = round((float) $payments->sum('amount'), 2);
            $totalOut = round(
                (float) $expenses->sum('total_amount') +
                (float) $cashReqs->sum(fn ($cr) => $cr->actual_amount ?? $cr->total_amount),
                2
            );

            // Fallback opening if no COA link
            if (!$ba->account_id) {
                $openingBalance = round((float) $ba->current_balance - $totalIn + $totalOut, 2);
            }

            $closingBalance = round($openingBalance + $totalIn - $totalOut, 2);

            // ── Build snapshot ────────────────────────────────────────────
            $snapshot = [
                'payments'  => $payments->map(fn ($p) => [
                    'time'          => $p->created_at?->format('H:i'),
                    'invoice'       => $p->invoice->invoice_number ?? null,
                    'client'        => $p->invoice->client->name ?? null,
                    'method'        => $p->payment_method,
                    'reference'     => $p->reference,
                    'recorded_by'   => $p->recordedBy->name ?? null,
                    'notes'         => $p->notes,
                    'amount'        => (float) $p->amount,
                ])->toArray(),

                'expenses'  => $expenses->map(fn ($e) => [
                    'time'          => $e->created_at?->format('H:i'),
                    'number'        => $e->expense_number,
                    'category'      => $e->category->name ?? null,
                    'description'   => $e->description,
                    'reference'     => $e->reference,
                    'posted_by'     => $e->createdBy->name ?? null,
                    'amount'        => (float) $e->amount,
                    'vat_amount'    => (float) $e->vat_amount,
                    'total_amount'  => (float) $e->total_amount,
                ])->toArray(),

                'cash_requests' => $cashReqs->map(fn ($cr) => [
                    'time'          => $cr->paid_at?->format('H:i'),
                    'number'        => $cr->request_number,
                    'purpose'       => $cr->purpose,
                    'requested_by'  => $cr->requestedBy->name ?? null,
                    'total_amount'  => (float) $cr->total_amount,
                    'actual_amount' => (float) ($cr->actual_amount ?? $cr->total_amount),
                ])->toArray(),
            ];

            // ── Upsert ────────────────────────────────────────────────────
            DailyClosing::updateOrCreate(
                ['bank_account_id' => $ba->id, 'closing_date' => $date],
                [
                    'opening_balance'      => $openingBalance,
                    'total_in'             => $totalIn,
                    'total_out'            => $totalOut,
                    'closing_balance'      => $closingBalance,
                    'payments_count'       => $payments->count(),
                    'expenses_count'       => $expenses->count(),
                    'cash_requests_count'  => $cashReqs->count(),
                    'snapshot'             => $snapshot,
                    'is_auto'              => $isAuto,
                    'closed_by'            => null,
                ]
            );

            $saved++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Saved: {$saved}, Skipped (already closed): {$skipped}.");
        $this->line("  Use --force to overwrite existing closings.");

        return self::SUCCESS;
    }
}
