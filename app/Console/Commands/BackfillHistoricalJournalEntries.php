<?php

namespace App\Console\Commands;

use App\Models\BankAccount;
use App\Models\InvoicePayment;
use App\Services\JournalEntryService;
use Illuminate\Console\Command;

class BackfillHistoricalJournalEntries extends Command
{
    protected $signature = 'je:backfill-historical
                            {--bank-id=1 : BankAccount ID to assign to all unlinked historical payments}
                            {--dry-run   : Preview without writing any data}';

    protected $description = 'Backfill journal entries for historical InvoicePayments that have no bank_account_id and no journal entry';

    public function handle(JournalEntryService $jeService): int
    {
        $bankId = (int) $this->option('bank-id');
        $dryRun = $this->option('dry-run');

        $bankAccount = BankAccount::find($bankId);
        if (!$bankAccount) {
            $this->error("BankAccount ID {$bankId} not found.");
            return self::FAILURE;
        }

        $this->info("Using bank account: {$bankAccount->bank_name} — {$bankAccount->account_name} (ID {$bankAccount->id})");

        // Find all historical payments with no JE (bank_account_id may already be set from a prior partial run)
        $payments = InvoicePayment::where(function ($q) use ($bankId) {
                $q->whereNull('bank_account_id')
                  ->orWhere('bank_account_id', $bankId);
            })
            ->whereNull('journal_entry_id')
            ->where('is_reversed', false)
            ->with(['invoice.items', 'invoice.booking'])
            ->get();

        if ($payments->isEmpty()) {
            $this->info('No unlinked historical payments found. Nothing to do.');
            return self::SUCCESS;
        }

        $this->info("Found {$payments->count()} payment(s) to process.");

        $successCount = 0;
        $errorCount   = 0;
        $totalAmount  = 0;

        foreach ($payments as $payment) {
            $invoice = $payment->invoice;
            if (!$invoice) {
                $this->warn("  [SKIP] Payment ID {$payment->id}: no invoice linked.");
                $errorCount++;
                continue;
            }

            $label = "Payment #{$payment->id} → Invoice {$invoice->invoice_number} (TZS " . number_format($payment->amount) . ")";

            if ($dryRun) {
                $this->line("  [DRY-RUN] Would process: {$label}");
                $totalAmount += (float) $payment->amount;
                $successCount++;
                continue;
            }

            try {
                // Assign bank account to payment
                $payment->update(['bank_account_id' => $bankAccount->id]);

                // Post the combined cash-sale JE
                $je = $jeService->onHistoricalSale($invoice, $bankAccount);

                if ($je) {
                    $payment->update(['journal_entry_id' => $je->id]);
                    $this->line("  [OK] {$label} → JE #{$je->id}");
                } else {
                    $this->warn("  [WARN] {$label} → JE service returned null (check COA setup).");
                    $errorCount++;
                    continue;
                }

                $totalAmount += (float) $payment->amount;
                $successCount++;
            } catch (\Throwable $e) {
                $this->error("  [ERROR] {$label}: " . $e->getMessage());
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info("Done. Success: {$successCount} | Errors: {$errorCount} | Total posted: TZS " . number_format($totalAmount));

        if ($dryRun) {
            $this->warn('Dry-run mode — no data was written. Remove --dry-run to apply.');
        }

        return self::SUCCESS;
    }
}
