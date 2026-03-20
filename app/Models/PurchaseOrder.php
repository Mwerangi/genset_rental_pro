<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number', 'supplier_id', 'status', 'notes',
        'ordered_at', 'expected_at', 'received_at', 'created_by',
    ];

    protected $casts = [
        'ordered_at'  => 'datetime',
        'expected_at' => 'date',
        'received_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (PurchaseOrder $po) {
            if (empty($po->po_number)) {
                $po->po_number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $year = now()->format('Y');
        $last = static::whereYear('created_at', $year)->max('id') ?? 0;
        return 'PO-' . $year . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function supplierPayments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalValueAttribute(): float
    {
        return round($this->items->sum(fn($i) => $i->quantity_ordered * $i->unit_cost), 2);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'     => 'Draft',
            'sent'      => 'Sent to Supplier',
            'partial'   => 'Partially Received',
            'received'  => 'Fully Received',
            'cancelled' => 'Cancelled',
            default     => ucfirst($this->status),
        };
    }

    public function getStatusStyleAttribute(): string
    {
        return match($this->status) {
            'draft'     => 'background:#f3f4f6;color:#374151;',
            'sent'      => 'background:#dbeafe;color:#1e40af;',
            'partial'   => 'background:#fef9c3;color:#854d0e;',
            'received'  => 'background:#dcfce7;color:#166534;',
            'cancelled' => 'background:#fee2e2;color:#991b1b;',
            default     => 'background:#f3f4f6;color:#374151;',
        };
    }
}
