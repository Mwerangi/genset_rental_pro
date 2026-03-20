<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = [
        'category_id', 'sku', 'name', 'description', 'unit',
        'current_stock', 'min_stock_level', 'unit_cost', 'notes', 'is_active',
    ];

    protected $casts = [
        'current_stock'   => 'decimal:3',
        'min_stock_level' => 'decimal:3',
        'unit_cost'       => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->min_stock_level > 0 && $this->current_stock <= $this->min_stock_level;
    }

    public function getUnitLabelAttribute(): string
    {
        return match($this->unit) {
            'pieces' => 'pcs',
            'litres' => 'L',
            'kg'     => 'kg',
            'metres' => 'm',
            'sets'   => 'sets',
            'pairs'  => 'pairs',
            'boxes'  => 'boxes',
            default  => $this->unit,
        };
    }

    /**
     * Adjust stock and record the movement atomically.
     */
    public function adjustStock(string $type, float $quantity, float $unitCost = 0, array $extra = []): StockMovement
    {
        $movement = $this->stockMovements()->create(array_merge([
            'type'      => $type,
            'quantity'  => $quantity,
            'unit_cost' => $unitCost,
        ], $extra));

        $delta = $type === 'out' ? -$quantity : $quantity;
        $this->increment('current_stock', $delta);

        return $movement;
    }
}
