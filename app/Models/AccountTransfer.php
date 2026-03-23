<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountTransfer extends Model
{
    protected $fillable = [
        'reference', 'from_bank_account_id', 'to_bank_account_id',
        'amount', 'transfer_date', 'description', 'journal_entry_id', 'created_by',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'transfer_date' => 'date',
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
}
