<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Genset extends Model
{
    protected $fillable = [
        'asset_number',
        'serial_number',
        'name',
        'type',
        'brand',
        'model',
        'kva_rating',
        'kw_rating',
        'fuel_type',
        'status',
        'current_booking_id',
        'color',
        'weight_kg',
        'dimensions',
        'tank_capacity_litres',
        'run_hours',
        'purchase_date',
        'purchase_price',
        'supplier',
        'warranty_expiry',
        'location',
        'daily_rate',
        'weekly_rate',
        'monthly_rate',
        'last_service_date',
        'next_service_date',
        'service_interval_hours',
        'notes',
    ];

    protected $casts = [
        'purchase_date'      => 'date',
        'warranty_expiry'    => 'date',
        'last_service_date'  => 'date',
        'next_service_date'  => 'date',
        'purchase_price'     => 'decimal:2',
        'daily_rate'         => 'decimal:2',
        'weekly_rate'        => 'decimal:2',
        'monthly_rate'       => 'decimal:2',
        'weight_kg'          => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($genset) {
            if (empty($genset->asset_number)) {
                $genset->asset_number = static::generateAssetNumber();
            }
        });
    }

    public static function generateAssetNumber(): string
    {
        $prefix = 'MP-';
        $last = static::where('asset_number', 'like', $prefix . '%')
            ->orderBy('asset_number', 'desc')
            ->first();

        $next = $last ? ((int) substr($last->asset_number, 3)) + 1 : 1;

        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    // Relationships

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function activeBooking()
    {
        return $this->hasOne(Booking::class)->where('status', 'active');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class);
    }

    // Computed attributes

    public function getTypeFormattedAttribute(): string
    {
        return match($this->type) {
            'clip-on'     => 'Clip-on (Container)',
            'underslung'  => 'Underslung',
            'open-frame'  => 'Open Frame',
            'canopy'      => 'Canopy / Silent',
            default       => ucfirst($this->type),
        };
    }

    public function getPowerRatingAttribute(): string
    {
        $parts = [];
        if ($this->kva_rating) $parts[] = $this->kva_rating . ' KVA';
        if ($this->kw_rating)  $parts[] = $this->kw_rating . ' KW';
        return implode(' / ', $parts) ?: '—';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'available'   => 'green',
            'rented'      => 'blue',
            'maintenance' => 'yellow',
            'repair'      => 'orange',
            'retired'     => 'gray',
            'reserved'    => 'purple',
            default       => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'available'   => 'Available',
            'rented'      => 'On Rent',
            'maintenance' => 'Maintenance',
            'repair'      => 'Under Repair',
            'retired'     => 'Retired',
            'reserved'    => 'Reserved',
            default       => ucfirst($this->status),
        };
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'available';
    }

    public function getWarrantyStatusAttribute(): string
    {
        if (!$this->warranty_expiry) return 'Unknown';
        if ($this->warranty_expiry->isPast()) return 'Expired';
        if ($this->warranty_expiry->diffInDays(now()) < 90) return 'Expiring Soon';
        return 'Active';
    }

    public function getServiceDueAttribute(): bool
    {
        return $this->next_service_date && $this->next_service_date->isPast();
    }
}
