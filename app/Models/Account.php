<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'code', 'name', 'type', 'sub_type', 'parent_id',
        'normal_balance', 'balance', 'description', 'is_active', 'is_system',
    ];

    protected $casts = [
        'balance'   => 'decimal:2',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    public function expenseCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    // ─── Accessors ───────────────────────────────────────────────────

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'asset'     => 'Asset',
            'liability' => 'Liability',
            'equity'    => 'Equity',
            'revenue'   => 'Revenue',
            'expense'   => 'Expense',
            default     => ucfirst($this->type),
        };
    }

    public function getTypeBadgeStyle(): string
    {
        return match ($this->type) {
            'asset'     => 'background:#dbeafe;color:#1e40af;',
            'liability' => 'background:#fee2e2;color:#991b1b;',
            'equity'    => 'background:#f3e8ff;color:#6b21a8;',
            'revenue'   => 'background:#dcfce7;color:#166534;',
            'expense'   => 'background:#fef9c3;color:#854d0e;',
            default     => 'background:#f3f4f6;color:#374151;',
        };
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->code} — {$this->name}";
    }

    // ─── Balance helpers ─────────────────────────────────────────────

    /**
     * Update the running balance after a JE line is posted.
     * Debits increase asset/expense, decrease liability/equity/revenue.
     * Credits do the opposite.
     */
    public function applyDebit(float $amount): void
    {
        if ($this->normal_balance === 'debit') {
            $this->increment('balance', $amount);
        } else {
            $this->decrement('balance', $amount);
        }
    }

    public function applyCredit(float $amount): void
    {
        if ($this->normal_balance === 'credit') {
            $this->increment('balance', $amount);
        } else {
            $this->decrement('balance', $amount);
        }
    }

    public function reverseDebit(float $amount): void
    {
        if ($this->normal_balance === 'debit') {
            $this->decrement('balance', $amount);
        } else {
            $this->increment('balance', $amount);
        }
    }

    public function reverseCredit(float $amount): void
    {
        if ($this->normal_balance === 'credit') {
            $this->decrement('balance', $amount);
        } else {
            $this->increment('balance', $amount);
        }
    }

    // ─── Scope ───────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
