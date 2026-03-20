<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    protected $fillable = [
        'maintenance_number',
        'genset_id',
        'booking_id',
        'type',
        'priority',
        'status',
        'title',
        'description',
        'reported_at',
        'scheduled_date',
        'started_at',
        'completed_at',
        'technician_name',
        'technician_phone',
        'parts_used',
        'cost',
        'run_hours_at_service',
        'next_service_date',
        'next_service_hours',
        'internal_notes',
        'created_by',
    ];

    protected $casts = [
        'reported_at'       => 'datetime',
        'scheduled_date'    => 'date',
        'started_at'        => 'datetime',
        'completed_at'      => 'datetime',
        'next_service_date' => 'date',
        'cost'              => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($record) {
            if (empty($record->maintenance_number)) {
                $record->maintenance_number = static::generateNumber();
            }
            if (empty($record->reported_at)) {
                $record->reported_at = now();
            }
        });
    }

    public static function generateNumber(): string
    {
        $year   = date('Y');
        $prefix = 'MR-' . $year . '-';
        $last   = static::where('maintenance_number', 'like', $prefix . '%')
            ->orderBy('maintenance_number', 'desc')->first();
        $next   = $last ? ((int) substr($last->maintenance_number, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    // ─── Relationships ───────────────────────────────────────────────────────

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

    // ─── Accessors ───────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return [
            'scheduled'   => 'Scheduled Service',
            'preventive'  => 'Preventive Maintenance',
            'repair'      => 'Repair',
            'breakdown'   => 'Breakdown',
            'inspection'  => 'Inspection',
        ][$this->type] ?? ucfirst($this->type);
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'scheduled'   => 'Scheduled',
            'in_progress' => 'In Progress',
            'completed'   => 'Completed',
            'cancelled'   => 'Cancelled',
        ][$this->status] ?? ucfirst($this->status);
    }

    public function getStatusStyleAttribute(): string
    {
        return [
            'scheduled'   => 'background:#dbeafe;color:#1e40af;',
            'in_progress' => 'background:#fef9c3;color:#854d0e;',
            'completed'   => 'background:#dcfce7;color:#166534;',
            'cancelled'   => 'background:#f3f4f6;color:#6b7280;',
        ][$this->status] ?? 'background:#f3f4f6;color:#374151;';
    }

    public function getPriorityLabelAttribute(): string
    {
        return [
            'low'      => 'Low',
            'medium'   => 'Medium',
            'high'     => 'High',
            'critical' => 'Critical',
        ][$this->priority] ?? ucfirst($this->priority);
    }

    public function getPriorityStyleAttribute(): string
    {
        return [
            'low'      => 'background:#f3f4f6;color:#6b7280;',
            'medium'   => 'background:#dbeafe;color:#1e40af;',
            'high'     => 'background:#fef9c3;color:#854d0e;',
            'critical' => 'background:#fee2e2;color:#991b1b;',
        ][$this->priority] ?? 'background:#f3f4f6;color:#374151;';
    }
}
