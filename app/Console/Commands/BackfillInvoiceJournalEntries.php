<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\JournalEntry;
use App\Services\JournalEntryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillInvoiceJournalEntries extends Command
{
    protected $signature = 'je:backfill-invoices
                            {--dry-run : Preview without writing any data}';

    protected $description = 'Backfill missing journal entries for regular (non-historical) invoices and their payments.
                              Run accounts:recalculate-balances afterwards to rebuild COA balances.';

    public function handle(JournalEntryService $jeService): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY-RUN mode — no data will be written.');
        }

        $this->newLine();

        // ── STEP 1: Revenue recognition JEs for invoices ─────────────────────
        // Find all non-historical invoices in sent/paid/partially_paid/disputed
        // status that have no JE with source_type='invoice' and source_id=invoice.id
        $this->info('Step 1: Revenue recognition JEs (onInvoiceSent) ...');

        $postedInvoiceIds = JournalEntry::where('source_type', 'invoice')
            ->where('status', 'posted')
            ->pluck('source_id')
            ->flip();

        $invoices = Invoice::whereIn('status', ['sent', 'paid', 'partially_paid', 'disputed'])
            ->whereHas('booking', fn($q) => $q->where('is_historical', false))
            ->orWhereDoesntHave('booking')
            ->with('items')
            ->get()
            ->filter(fn($inv) => !$postedInvoiceIds->has($inv->id));

        // Re-filter: only keep non-historical ones (the orWhereDoesntHave may include historicals)
        $invoices = $invoices->filter(function ($inv) {
            return !($inv->booking?->is_historical ?? false);
        })->filter(fn($inv) => in_array($inv->status, ['sent', 'paid', 'partially_paid', 'disputed']));

        $this->info("  Found {$invoices->count()} invoice(s) missing revenue JEs.");

        $invOk = 0; $invErr = 0;
        foreach ($invoices as $invoice) {
            $label = "Invoice {$invoice->invoice_number} ({$invoice->status}, TZS " . number_format($invoice->total_amount) . ")";
            if ($dryRun) {
                $this->line("  [DRY] {$label}");
                $invOk++;
                continue;
            }
            try {
                // Mark sent_at if not set so the record is consistent
                if (!$invoice->sent_at) {
                    $invoice->update(['sent_at' => $invoice->issue_date ?? now()]);
                }
                $je = $jeService->onInvoiceSent($invoice);
                if ($je) {
                    $this->line("  [OK]  {$label} → JE {$je->entry_number}");
                    $invOk++;
                } else {
                    $this->warn("  [WARN] {$label} → null (check COA: 1140, 4100, 2120)");
                    $invErr++;
                }
            } catch (\Throwable $e) {
                $this->error("  [ERR] {$label}: " . $e->getMessage());
                $invErr++;
            }
        }

        $this->info("  Revenue JEs: {$invOk} posted, {$invErr} failed.");
        $this->newLine();

        // ── STEP 2: Payment receipt JEs ───────────────────────────────────────
        // Find all non-historical InvoicePayments with no journal_entry_id
        // that have a bank_account_id set (required for onPaymentRecorded)
        $this->info('Step 2: Payment receipt JEs (onPaymentRecorded) ...');

        $payments = InvoicePayment::whereNull('journal_entry_id')
            ->whereNotNull('bank_account_id')
            ->where('is_reversed', false)
            ->whereHas('invoice', function ($q) {
                $q->whereIn('status', ['sent', 'paid', 'partially_paid', 'disputed'])
                  ->whereHas('booking', fn($bq) => $bq->where('is_historical', false));
            })
            ->with(['invoice.items', 'invoice.booking', 'bankAccount'])
            ->get();

        $this->info("  Found {$payments->count()} payment(s) missing receipt JEs.");

        $payOk = 0; $payErr = 0;
        foreach ($payments as $payment) {
            $label = "Payment #{$payment->id} on {$payment->invoice?->invoice_number} (TZS " . number_format($payment->amount) . ")";
            if ($dryRun) {
                $this->line("  [DRY] {$label}");
                $payOk++;
                continue;
            }
            try {
                // Ensure the revenue JE exists first so AR can be credited
                $invoice = $payment->invoice;
                if ($invoice && !JournalEntry::where('source_type', 'invoice')->where('source_id', $invoice->id)->where('status', 'posted')->exists()) {
                    if (!$invoice->sent_at) {
                        $invoice->update(['sent_at' => $invoice->issue_date ?? now()]);
                    }
                    $jeService->onInvoiceSent($invoice);
                }

                $je = $jeService->onPaymentRecorded($payment);
                if ($je) {
                    $payment->update(['journal_entry_id' => $je->id]);
                    $this->line("  [OK]  {$label} → JE {$je->entry_number}");
                    $payOk++;
                } else {
                    $this->warn("  [WARN] {$label} → null (check COA: bank account linked, 1140 exists)");
                    $payErr++;
                }
            } catch (\Throwable $e) {
                $this->error("  [ERR] {$label}: " . $e->getMessage());
                $payErr++;
            }
        }

        $this->info("  Payment JEs: {$payOk} posted, {$payErr} failed.");
        $this->newLine();

        // ── Summary ───────────────────────────────────────────────────────────
        $totalOk  = $invOk + $payOk;
        $totalErr = $invErr + $payErr;

        $this->info("────────────────────────────────────────────");
        $this->info("Total JEs posted: {$totalOk} | Errors: {$totalErr}");

        if ($totalErr > 0) {
            $this->warn("Some records failed — most likely a missing COA account (1140 AR, 4100 Revenue, 2120 VAT, or bank account not linked to a COA account).");
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('Dry-run complete — re-run without --dry-run to apply changes.');
        } else {
            $this->newLine();
            $this->info('Now run:  php artisan accounts:recalculate-balances');
            $this->info('to rebuild all COA account balances from the newly posted JEs.');
        }

        return $totalErr > 0 ? self::FAILURE : self::SUCCESS;
    }
}
