<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashRequestItem extends Model
{
    protected $fillable = [
        'cash_request_id', 'description', 'estimated_amount',
        'vat_amount', 'is_zero_rated', 'vat_justification', 'total_amount',
        'actual_amount', 'receipt_ref', 'receipt_path', 'expense_category_id',
    ];

    protected $casts = [
        'estimated_amount' => 'decimal:2',
        'vat_amount'       => 'decimal:2',
        'total_amount'     => 'decimal:2',
        'actual_amount'    => 'decimal:2',
        'is_zero_rated'    => 'boolean',
    ];

    public function cashRequest(): BelongsTo
    {
        return $this->belongsTo(CashRequest::class);
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }
}
