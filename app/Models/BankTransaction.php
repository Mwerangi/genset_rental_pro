<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    protected $fillable = [
        'bank_statement_id', 'transaction_date', 'description', 'reference',
        'amount', 'type', 'status', 'contra_account_id', 'journal_entry_id',
        'partner_type', 'partner_id', 'notes',
        'reconciled_payment_type', 'reconciled_payment_id', 'reconciled_at', 'reconciled_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount'           => 'decimal:2',
        'reconciled_at'    => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function bankStatement(): BelongsTo
    {
        return $this->belongsTo(BankStatement::class);
    }

    public function contraAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'contra_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    /**
     * Returns the InvoicePayment or SupplierPayment this transaction was reconciled against.
     */
    public function reconciledPayment(): ?Model
    {
        if (!$this->reconciled_payment_type || !$this->reconciled_payment_id) return null;

        $allowed = [
            \App\Models\InvoicePayment::class  => \App\Models\InvoicePayment::class,
            \App\Models\SupplierPayment::class => \App\Models\SupplierPayment::class,
            \App\Models\Expense::class         => \App\Models\Expense::class,
        ];

        $class = $allowed[$this->reconciled_payment_type] ?? null;
        if (!$class) return null;

        return $class::find($this->reconciled_payment_id);
    }

    public function partner(): ?BelongsTo
    {
        if ($this->partner_type === 'client')   return $this->belongsTo(Client::class, 'partner_id');
        if ($this->partner_type === 'supplier') return $this->belongsTo(Supplier::class, 'partner_id');
        return null;
    }

    // ── Accessors ────────────────────────────────────────────────────

    public function getPartnerNameAttribute(): ?string
    {
        if (!$this->partner_type || !$this->partner_id) return null;
        $rel = $this->partner();
        if (!$rel) return null;
        $m = $rel->first();
        return $m?->company_name ?? $m?->name ?? null;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'posted'       => 'bg-green-100 text-green-700',
            'ignored'      => 'bg-gray-100 text-gray-500',
            'reconciled'   => 'bg-purple-100 text-purple-700',
            default        => 'bg-yellow-100 text-yellow-700',
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return $this->type === 'credit'
            ? 'bg-blue-100 text-blue-700'
            : 'bg-red-100 text-red-700';
    }
}
