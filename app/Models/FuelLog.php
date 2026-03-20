<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelLog extends Model
{
    protected $fillable = [
        'genset_id', 'booking_id', 'litres', 'cost_per_litre', 'total_cost',
        'run_hours_before', 'run_hours_after', 'fuelled_at',
        'fuelled_by', 'location', 'notes', 'created_by',
    ];

    protected $casts = [
        'litres'           => 'decimal:2',
        'cost_per_litre'   => 'decimal:2',
        'total_cost'       => 'decimal:2',
        'run_hours_before' => 'decimal:2',
        'run_hours_after'  => 'decimal:2',
        'fuelled_at'       => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (FuelLog $log) {
            // Auto-calculate total cost if not set
            if (empty($log->total_cost) || $log->total_cost == 0) {
                $log->total_cost = round($log->litres * $log->cost_per_litre, 2);
            }
        });
    }

    public function genset(): BelongsTo
    {
        return $this->belongsTo(Genset::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Consumption rate in litres/hour for this fuelling event.
     * Requires run_hours_before + run_hours_after to calculate.
     */
    public function getConsumptionRateAttribute(): ?float
    {
        if ($this->run_hours_before !== null && $this->run_hours_after !== null) {
            $hours = $this->run_hours_after - $this->run_hours_before;
            if ($hours > 0) {
                return round($this->litres / $hours, 2);
            }
        }
        return null;
    }
}
