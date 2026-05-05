<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Expense extends Model
{
    protected $fillable = [
        'expense_number', 'expense_category_id', 'bank_account_id', 'supplier_id',
        'description', 'amount', 'vat_amount', 'is_zero_rated', 'total_amount',
        'expense_date', 'reference', 'attachment',
        'source_type', 'source_id', 'journal_entry_id',
        'status', 'created_by', 'approved_by', 'approved_at',
        'bank_reconciled_at', 'bank_reconciled_by',
    ];

    protected $casts = [
        'amount'             => 'decimal:2',
        'vat_amount'         => 'decimal:2',
        'total_amount'       => 'decimal:2',
        'expense_date'       => 'date',
        'approved_at'        => 'datetime',
        'bank_reconciled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($e) {
            if (empty($e->expense_number)) {
                $e->expense_number = static::generateExpenseNumber();
            }
            // Inherit zero-rated status from category if not explicitly set
            if (empty($e->is_zero_rated) && $e->expense_category_id) {
                $cat = ExpenseCategory::find($e->expense_category_id);
                if ($cat && $cat->is_zero_rated) {
                    $e->is_zero_rated = true;
                    $e->vat_amount    = 0;
                }
            }
            if (empty($e->total_amount)) {
                $e->total_amount = (float) $e->amount + (float) ($e->vat_amount ?? 0);
            }
        });
    }

    public static function generateExpenseNumber(): string
    {
        $year   = date('Y');
        $month  = date('m');
        $prefix = 'EXP-' . $year . '-' . $month . '-';
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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
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

    public function bankReconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bank_reconciled_by');
    }

    /**
     * The bank statement transaction this expense was reconciled to (if any).
     */
    public function bankTransaction(): HasOne
    {
        return $this->hasOne(BankTransaction::class, 'reconciled_payment_id')
                    ->where('reconciled_payment_type', static::class);
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
