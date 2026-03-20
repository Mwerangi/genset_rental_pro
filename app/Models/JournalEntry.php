<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_number', 'entry_date', 'description', 'reference',
        'source_type', 'source_id', 'status', 'is_reversed',
        'reversed_by_id', 'notes', 'created_by', 'posted_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'is_reversed' => 'boolean',
        'posted_at'  => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($je) {
            if (empty($je->entry_number)) {
                $je->entry_number = static::generateEntryNumber();
            }
        });
    }

    public static function generateEntryNumber(): string
    {
        $year = date('Y');
        $prefix = 'JE-' . $year . '-';
        $last = static::where('entry_number', 'like', $prefix . '%')
            ->orderBy('entry_number', 'desc')->first();
        $n = $last ? ((int) substr($last->entry_number, -4)) + 1 : 1;
        return $prefix . str_pad($n, 4, '0', STR_PAD_LEFT);
    }

    // ─── Relationships ───────────────────────────────────────────────

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reversed_by_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public function isBalanced(): bool
    {
        $totals = $this->lines()->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')->first();
        return round((float) $totals->total_debit, 2) === round((float) $totals->total_credit, 2);
    }

    /**
     * Post this entry: validate it's balanced, update account balances,
     * update bank_account balance if applicable, mark as posted.
     */
    public function post(): bool
    {
        if ($this->status === 'posted') return true;
        if (!$this->isBalanced()) return false;

        \DB::transaction(function () {
            foreach ($this->lines()->with('account')->get() as $line) {
                if ($line->debit > 0) $line->account->applyDebit((float) $line->debit);
                if ($line->credit > 0) $line->account->applyCredit((float) $line->credit);
            }
            $this->update(['status' => 'posted', 'posted_at' => now()]);
        });

        return true;
    }

    /**
     * Reverse a posted entry by creating a mirror entry with swapped debits/credits.
     */
    public function reverse(string $reason = '', ?int $userId = null): JournalEntry
    {
        return \DB::transaction(function () use ($reason, $userId) {
            $reversal = static::create([
                'entry_date'   => now(),
                'description'  => 'Reversal of ' . $this->entry_number . ($reason ? ': ' . $reason : ''),
                'source_type'  => $this->source_type,
                'source_id'    => $this->source_id,
                'status'       => 'draft',
                'created_by'   => $userId,
            ]);

            foreach ($this->lines as $line) {
                $reversal->lines()->create([
                    'account_id'  => $line->account_id,
                    'description' => $line->description,
                    'debit'       => $line->credit,
                    'credit'      => $line->debit,
                ]);
            }

            $reversal->post();
            $this->update(['is_reversed' => true, 'reversed_by_id' => $reversal->id]);

            return $reversal;
        });
    }

    // ─── Accessors ───────────────────────────────────────────────────

    public function getTotalDebitAttribute(): float
    {
        return (float) $this->lines->sum('debit');
    }

    public function getTotalCreditAttribute(): float
    {
        return (float) $this->lines->sum('credit');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'  => 'Draft',
            'posted' => 'Posted',
            default  => ucfirst($this->status),
        };
    }

    public function getStatusStyleAttribute(): string
    {
        return match ($this->status) {
            'draft'  => 'background:#f3f4f6;color:#374151;',
            'posted' => 'background:#dcfce7;color:#166534;',
            default  => 'background:#f3f4f6;color:#374151;',
        };
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source_type) {
            'invoice'        => 'Invoice',
            'payment'        => 'Payment',
            'purchase_order' => 'Purchase Order',
            'expense'        => 'Expense',
            'cash_request'   => 'Cash Request',
            'credit_note'    => 'Credit Note',
            'supplier_payment' => 'Supplier Payment',
            'manual'         => 'Manual Entry',
            default          => $this->source_type ? ucfirst(str_replace('_', ' ', $this->source_type)) : 'Manual',
        };
    }
}
