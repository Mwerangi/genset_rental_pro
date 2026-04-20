<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountTransfer extends Model
{
    protected $fillable = [
        'reference', 'from_bank_account_id', 'to_bank_account_id',
        'amount', 'to_amount', 'exchange_rate', 'from_currency', 'to_currency',
        'transfer_date', 'description', 'journal_entry_id', 'created_by',
        'reversed_at', 'reversed_by', 'reversal_of_transfer_id',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'to_amount'     => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'transfer_date' => 'date',
        'reversed_at'   => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $t) {
            if (empty($t->reference)) {
                $t->reference = static::generateReference();
            }
        });
    }

    public static function generateReference(): string
    {
        $year   = date('Y');
        $prefix = 'TRF-' . $year . '-';
        $last   = static::where('reference', 'like', $prefix . '%')
                        ->orderBy('reference', 'desc')->first();
        $n = $last ? ((int) substr($last->reference, -4)) + 1 : 1;
        return $prefix . str_pad($n, 4, '0', STR_PAD_LEFT);
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'from_bank_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'to_bank_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** True when source and destination are in different currencies */
    public function isFxTransfer(): bool
    {
        return $this->from_currency && $this->to_currency
            && $this->from_currency !== $this->to_currency;
    }

    /** True if this transfer has been reversed */
    public function isReversed(): bool
    {
        return $this->reversed_at !== null;
    }

    /** The original transfer this record reverses (if any) */
    public function originalTransfer(): BelongsTo
    {
        return $this->belongsTo(AccountTransfer::class, 'reversal_of_transfer_id');
    }

    /** Amount that actually leaves the source account */
    public function sourceAmount(): float
    {
        return (float) $this->amount;
    }

    /** Amount that arrives at the destination account */
    public function destinationAmount(): float
    {
        return $this->to_amount ? (float) $this->to_amount : (float) $this->amount;
    }
}
