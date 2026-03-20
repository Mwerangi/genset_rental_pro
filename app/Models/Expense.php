<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'expense_number', 'expense_category_id', 'bank_account_id',
        'description', 'amount', 'vat_amount', 'total_amount',
        'expense_date', 'reference', 'attachment',
        'source_type', 'source_id', 'journal_entry_id',
        'status', 'created_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'vat_amount'   => 'decimal:2',
        'total_amount' => 'decimal:2',
        'expense_date' => 'date',
        'approved_at'  => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($e) {
            if (empty($e->expense_number)) {
                $e->expense_number = static::generateExpenseNumber();
            }
            if (empty($e->total_amount)) {
                $e->total_amount = (float) $e->amount + (float) ($e->vat_amount ?? 0);
            }
        });
    }

    public static function generateExpenseNumber(): string
    {
        $year = date('Y');
        $prefix = 'EXP-' . $year . '-';
        $last = static::where('expense_number', 'like', $prefix . '%')
            ->orderBy('expense_number', 'desc')->first();
        $n = $last ? ((int) substr($last->expense_number, -4)) + 1 : 1;
        return $prefix . str_pad($n, 4, '0', STR_PAD_LEFT);
    }

    // ─── Relationships ───────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ─── Accessors ───────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'    => 'Draft',
            'approved' => 'Approved',
            'posted'   => 'Posted',
            default    => ucfirst($this->status),
        };
    }

    public function getStatusStyleAttribute(): string
    {
        return match ($this->status) {
            'draft'    => 'background:#f3f4f6;color:#374151;',
            'approved' => 'background:#fef9c3;color:#854d0e;',
            'posted'   => 'background:#dcfce7;color:#166534;',
            default    => 'background:#f3f4f6;color:#374151;',
        };
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source_type) {
            'fuel_log'    => 'Fuel Log',
            'maintenance' => 'Maintenance',
            'cash_request' => 'Cash Request',
            'manual'      => 'Manual',
            default       => 'Manual',
        };
    }
}
