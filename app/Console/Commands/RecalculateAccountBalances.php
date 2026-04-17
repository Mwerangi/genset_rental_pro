<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateAccountBalances extends Command
{
    protected $signature   = 'accounts:recalculate-balances';
    protected $description = 'Rebuild every COA account balance by summing all posted journal entry lines.';

    public function handle(): int
    {
        $this->info('Recalculating COA account balances from posted journal entries...');

        // Sum debits and credits per account from all posted JEs
        $lines = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.status', 'posted')
            ->select(
                'journal_entry_lines.account_id',
                DB::raw('SUM(journal_entry_lines.debit)  as total_debit'),
                DB::raw('SUM(journal_entry_lines.credit) as total_credit')
            )
            ->groupBy('journal_entry_lines.account_id')
            ->get()
            ->keyBy('account_id');

        $accounts = DB::table('accounts')->get();
        $updated  = 0;

        foreach ($accounts as $account) {
            $totals     = $lines->get($account->id);
            $totalDebit = $totals ? (float) $totals->total_debit  : 0.0;
            $totalCredit= $totals ? (float) $totals->total_credit : 0.0;

            // Balance = net movement in the account's favour based on normal_balance type
            // Debit-normal accounts (assets, expenses): balance = debits - credits
            // Credit-normal accounts (liabilities, equity, revenue): balance = credits - debits
            $balance = $account->normal_balance === 'debit'
                ? $totalDebit - $totalCredit
                : $totalCredit - $totalDebit;

            DB::table('accounts')
                ->where('id', $account->id)
                ->update(['balance' => $balance]);

            $updated++;
        }

        $this->info("Done. {$updated} account(s) recalculated.");

        return self::SUCCESS;
    }
}
