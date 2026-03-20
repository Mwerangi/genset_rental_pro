<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRequest extends Model
{
    protected $fillable = [
        'request_number', 'requested_by', 'purpose', 'total_amount',
        'actual_amount', 'status', 'bank_account_id',
        'approved_by', 'approved_at', 'paid_at', 'retired_at',
        'notes', 'rejection_reason', 'journal_entry_id', 'retire_journal_entry_id',
    ];

    protected $casts = [
        'total_amount'  => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'approved_at'   => 'datetime',
        'paid_at'       => 'datetime',
        'retired_at'    => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cr) {
            if (empty($cr->request_number)) {
                $cr->request_number = static::generateRequestNumber();
            }
        });
    }

    public static function generateRequestNumber(): string
    {
        $year = date('Y');
        $prefix = 'CR-' . $year . '-';
        $last = static::where('request_number', 'like', $prefix . '%')
            ->orderBy('request_number', 'desc')->first();
        $n = $last ? ((int) substr($last->request_number, -4)) + 1 : 1;
        return $prefix . str_pad($n, 4, '0', STR_PAD_LEFT);
    }

    // ─── Relationships ───────────────────────────────────────────────

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function retireJournalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'retire_journal_entry_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CashRequestItem::class);
    }

    // ─── Accessors ───────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'    => 'Draft',
            'pending'  => 'Pending Approval',
            'approved' => 'Approved',
            'paid'     => 'Disbursed',
            'retired'  => 'Retired / Reconciled',
            'rejected' => 'Rejected',
            default    => ucfirst($this->status),
        };
    }

    public function getStatusStyleAttribute(): string
    {
        return match ($this->status) {
            'draft'    => 'background:#f3f4f6;color:#374151;',
            'pending'  => 'background:#fef9c3;color:#854d0e;',
            'approved' => 'background:#dbeafe;color:#1e40af;',
            'paid'     => 'background:#dcfce7;color:#166534;',
            'retired'  => 'background:#d1fae5;color:#065f46;',
            'rejected' => 'background:#fee2e2;color:#991b1b;',
            default    => 'background:#f3f4f6;color:#374151;',
        };
    }
}
