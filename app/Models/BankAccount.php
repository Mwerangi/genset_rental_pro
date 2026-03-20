<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    protected $fillable = [
        'name', 'account_type', 'bank_name', 'account_number',
        'account_id', 'currency', 'current_balance', 'is_active', 'notes',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function invoicePayments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function supplierPayments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function cashRequests(): HasMany
    {
        return $this->hasMany(CashRequest::class);
    }

    // ─── Accessors ───────────────────────────────────────────────────

    public function getTypeLabel(): string
    {
        return match ($this->account_type) {
            'bank'         => 'Bank',
            'cash'         => 'Cash',
            'mobile_money' => 'Mobile Money',
            default        => ucfirst($this->account_type),
        };
    }

    public function getTypeBadgeStyle(): string
    {
        return match ($this->account_type) {
            'bank'         => 'background:#dbeafe;color:#1e40af;',
            'cash'         => 'background:#dcfce7;color:#166534;',
            'mobile_money' => 'background:#f3e8ff;color:#6b21a8;',
            default        => 'background:#f3f4f6;color:#374151;',
        };
    }
}
