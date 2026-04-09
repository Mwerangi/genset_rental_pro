<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    protected $fillable = [
        'journal_entry_id', 'account_id', 'description',
        'partner_type', 'partner_id',
        'debit', 'credit',
    ];

    protected $casts = [
        'debit'  => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function partner(): BelongsTo|null
    {
        if ($this->partner_type === 'client') {
            return $this->belongsTo(\App\Models\Client::class, 'partner_id');
        }
        if ($this->partner_type === 'supplier') {
            return $this->belongsTo(\App\Models\Supplier::class, 'partner_id');
        }
        return null;
    }

    public function getPartnerNameAttribute(): ?string
    {
        if (!$this->partner_type || !$this->partner_id) return null;
        $relation = $this->partner();
        if (!$relation) return null;
        $model = $relation->first();
        return $model?->company_name ?? $model?->name ?? null;
    }
}
