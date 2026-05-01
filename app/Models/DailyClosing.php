<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyClosing extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'closing_date'    => 'date',
        'opening_balance' => 'decimal:2',
        'total_in'        => 'decimal:2',
        'total_out'       => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'snapshot'        => 'array',
        'is_auto'         => 'boolean',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Find the closing record for a specific account and date.
     */
    public static function forAccountDate(int $bankAccountId, string $date): ?self
    {
        return static::where('bank_account_id', $bankAccountId)
            ->whereDate('closing_date', $date)
            ->first();
    }
}
