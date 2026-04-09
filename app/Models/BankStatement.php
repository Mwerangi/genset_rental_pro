<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankStatement extends Model
{
    protected $fillable = [
        'bank_account_id', 'created_by', 'reference',
        'period_from', 'period_to', 'notes',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to'   => 'date',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    // ── Counts ───────────────────────────────────────────────────────
    public function getPendingCountAttribute(): int
    {
        return $this->transactions()->where('status', 'pending')->count();
    }

    public function getPostedCountAttribute(): int
    {
        return $this->transactions()->where('status', 'posted')->count();
    }

    public function getIgnoredCountAttribute(): int
    {
        return $this->transactions()->where('status', 'ignored')->count();
    }
}
