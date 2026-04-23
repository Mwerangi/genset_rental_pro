<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill is_reversed + reversed_by_id on journal_entries for any account
 * transfer reversals that were created before the AccountTransferController
 * fix (commit c0d350a). Idempotent — already-fixed rows are skipped.
 */
return new class extends Migration
{
    public function up(): void
    {
        // For every reversal AccountTransfer record, find the original
        // transfer's JE and mark it as reversed.
        $reversalTransfers = DB::table('account_transfers')
            ->whereNotNull('reversal_of_transfer_id')
            ->whereNotNull('journal_entry_id')
            ->get();

        foreach ($reversalTransfers as $rev) {
            $original = DB::table('account_transfers')
                ->where('id', $rev->reversal_of_transfer_id)
                ->first();

            if (!$original || !$original->journal_entry_id) continue;

            // Idempotent: only update rows not already marked reversed
            DB::table('journal_entries')
                ->where('id', $original->journal_entry_id)
                ->where('is_reversed', false)
                ->update([
                    'is_reversed'    => true,
                    'reversed_by_id' => $rev->journal_entry_id,
                    'updated_at'     => now(),
                ]);
        }
    }

    public function down(): void
    {
        // Not reversible — we cannot know which rows were backfilled vs
        // correctly set by the fixed controller.
    }
};
