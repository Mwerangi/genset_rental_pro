<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'item_type',
        'description',
        'quantity',
        'unit_price',
        'duration_days',
        'subtotal',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'unit_price'   => 'decimal:2',
        'duration_days'=> 'integer',
        'subtotal'     => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getItemTypeLabelAttribute(): string
    {
        return match ($this->item_type) {
            'genset_rental' => 'Genset Rental',
            'delivery'      => 'Delivery / Transport',
            'fuel'          => 'Fuel',
            'maintenance'   => 'Maintenance',
            'extra_days'    => 'Extra Days (Overstay)',
            'damage'        => 'Damage Charge',
            'penalty'       => 'Penalty / Late Fee',
            'credit'        => 'Credit / Deduction',
            'other'         => 'Other',
            default         => ucfirst($this->item_type),
        };
    }
}
