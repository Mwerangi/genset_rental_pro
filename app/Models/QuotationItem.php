<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id',
        'item_type',
        'description',
        'quantity',
        'unit_price',
        'duration_days',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'duration_days' => 'integer',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Boot method to auto-calculate subtotal
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->calculateSubtotal();
        });

        static::saved(function ($item) {
            // Recalculate quotation totals when item is saved
            if ($item->quotation) {
                $item->quotation->calculateTotals();
            }
        });

        static::deleted(function ($item) {
            // Recalculate quotation totals when item is deleted
            if ($item->quotation) {
                $item->quotation->calculateTotals();
            }
        });
    }

    /**
     * Relationships
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * Helper methods
     */
    public function calculateSubtotal()
    {
        if ($this->item_type === 'genset_rental' && $this->duration_days) {
            // For rental: unit_price * quantity * duration_days
            $this->subtotal = $this->unit_price * $this->quantity * $this->duration_days;
        } else {
            // For other items: unit_price * quantity
            $this->subtotal = $this->unit_price * $this->quantity;
        }
    }

    /**
     * Attributes
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'TZS ' . number_format($this->subtotal, 2);
    }

    public function getItemTypeFormattedAttribute(): string
    {
        return match($this->item_type) {
            'genset_rental' => 'Generator Rental',
            'delivery' => 'Delivery & Setup',
            'fuel' => 'Fuel Package',
            'maintenance' => 'Maintenance/Support',
            'other' => 'Other',
            default => ucfirst($this->item_type),
        };
    }
}
