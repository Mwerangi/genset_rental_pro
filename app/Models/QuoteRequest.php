<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class QuoteRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'request_number',
        'client_id',
        'full_name',
        'email',
        'phone',
        'company_name',
        'genset_type',
        'rental_start_date',
        'rental_duration_days',
        'delivery_location',
        'pickup_location',
        'site_location',
        'additional_requirements',
        'status',
        'source',
        'ip_address',
        'user_agent',
        'reviewed_at',
        'reviewed_by',
        'rejection_reason',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'rental_start_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quoteRequest) {
            if (empty($quoteRequest->request_number)) {
                $quoteRequest->request_number = self::generateRequestNumber();
            }
        });
    }

    /**
     * Generate unique request number.
     */
    public static function generateRequestNumber(): string
    {
        $year = date('Y');
        $prefix = "QR-{$year}-";
        
        // Get the last request number for this year
        $lastRequest = self::where('request_number', 'like', "{$prefix}%")
            ->orderBy('request_number', 'desc')
            ->first();
        
        if ($lastRequest) {
            $lastNumber = (int) substr($lastRequest->request_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $newNumber;
    }

    /**
     * Get the client created from this quote request.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user who reviewed this request.
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get all quotations generated from this request.
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    /**
     * Scope for new requests.
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope for reviewed requests.
     */
    public function scopeReviewed($query)
    {
        return $query->where('status', 'reviewed');
    }

    /**
     * Scope for quoted requests.
     */
    public function scopeQuoted($query)
    {
        return $query->where('status', 'quoted');
    }

    /**
     * Mark request as reviewed.
     */
    public function markAsReviewed($userId = null): void
    {
        $this->update([
            'status' => 'reviewed',
            'reviewed_at' => now(),
            'reviewed_by' => $userId,
        ]);
    }

    /**
     * Get formatted genset type.
     */
    public function getGensetTypeFormattedAttribute(): string
    {
        return match($this->genset_type) {
            'clip-on' => 'Clip-on Generator (20ESX)',
            'underslung' => 'Underslung Generator',
            'not_sure' => 'Not Sure / Need Advice',
            '10kva' => '10 KVA',
            '20kva' => '20 KVA',
            '30kva' => '30 KVA',
            '45kva' => '45 KVA',
            '60kva' => '60 KVA',
            '100kva' => '100+ KVA',
            default => ucfirst($this->genset_type),
        };
    }

    /**
     * Get calculated rental end date.
     */
    public function getRentalEndDateAttribute()
    {
        return $this->rental_start_date->addDays($this->rental_duration_days);
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'new' => 'red',
            'reviewed' => 'yellow',
            'quoted' => 'blue',
            'converted' => 'green',
            'rejected' => 'gray',
            default => 'gray',
        };
    }
}
