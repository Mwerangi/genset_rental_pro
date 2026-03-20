<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'inventory_item_id', 'type', 'quantity', 'unit_cost',
        'maintenance_record_id', 'purchase_order_id', 'reference', 'notes', 'created_by',
    ];

    protected $casts = [
        'quantity'  => 'decimal:3',
        'unit_cost' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function maintenanceRecord(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRecord::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'in'         => 'Stock In',
            'out'        => 'Stock Out',
            'adjustment' => 'Adjustment',
            default      => ucfirst($this->type),
        };
    }

    public function getTypeStyleAttribute(): string
    {
        return match($this->type) {
            'in'         => 'background:#dcfce7;color:#166534;',
            'out'        => 'background:#fee2e2;color:#991b1b;',
            'adjustment' => 'background:#fef9c3;color:#854d0e;',
            default      => 'background:#f3f4f6;color:#374151;',
        };
    }

    public function getTotalCostAttribute(): float
    {
        return round($this->quantity * $this->unit_cost, 2);
    }
}
