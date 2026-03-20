<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $fillable = [
        'delivery_number',
        'booking_id',
        'genset_id',
        'type',
        'status',
        'driver_name',
        'driver_phone',
        'vehicle_details',
        'origin_address',
        'destination_address',
        'scheduled_at',
        'dispatched_at',
        'completed_at',
        'notes',
        'pod_notes',
        'pod_signed_by',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at'  => 'datetime',
        'dispatched_at' => 'datetime',
        'completed_at'  => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($delivery) {
            if (empty($delivery->delivery_number)) {
                $delivery->delivery_number = static::generateNumber($delivery->type ?? 'delivery');
            }
        });
    }

    public static function generateNumber(string $type = 'delivery'): string
    {
        $year   = date('Y');
        $prefix = ($type === 'return' ? 'RT' : 'DL') . '-' . $year . '-';
        $last   = static::where('delivery_number', 'like', $prefix . '%')
            ->orderBy('delivery_number', 'desc')->first();
        $next   = $last ? ((int) substr($last->delivery_number, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    // ─── Relationships ──────────────────────────────────────────────────────

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function genset(): BelongsTo
    {
        return $this->belongsTo(Genset::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return ['delivery' => 'Delivery', 'return' => 'Return Pickup'][$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'pending'    => 'Pending',
            'dispatched' => 'Dispatched',
            'completed'  => 'Completed',
            'failed'     => 'Failed',
        ][$this->status] ?? ucfirst($this->status);
    }

    public function getStatusStyleAttribute(): string
    {
        return [
            'pending'    => 'background:#fef9c3;color:#854d0e;',
            'dispatched' => 'background:#dbeafe;color:#1e40af;',
            'completed'  => 'background:#dcfce7;color:#166534;',
            'failed'     => 'background:#fee2e2;color:#991b1b;',
        ][$this->status] ?? 'background:#f3f4f6;color:#374151;';
    }
}
