<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_number',
        'company_name',
        'full_name',
        'email',
        'phone',
        'tin_number',
        'vrn',
        'status',
        'risk_level',
        'credit_limit',
        'payment_terms_days',
        'notes',
        'source',
        'quote_request_id',
        'created_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            if (empty($client->client_number)) {
                $client->client_number = static::generateClientNumber();
            }
        });
    }

    public static function generateClientNumber(): string
    {
        $year = date('Y');
        $prefix = 'CL-' . $year . '-';

        $last = static::withTrashed()
            ->where('client_number', 'like', $prefix . '%')
            ->orderBy('client_number', 'desc')
            ->first();

        $newNumber = $last ? ((int) substr($last->client_number, -4)) + 1 : 1;

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    public function primaryContact()
    {
        return $this->hasOne(ClientContact::class)->where('is_primary', true);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(ClientAddress::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    // ─── Computed Attributes ──────────────────────────────────────────────────

    public function getDisplayNameAttribute(): string
    {
        return $this->company_name ?: $this->full_name;
    }

    public function getTotalSpendAttribute(): float
    {
        return (float) $this->bookings()->whereIn('status', ['invoiced', 'paid'])->sum('total_amount');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active'      => 'green',
            'inactive'    => 'gray',
            'blacklisted' => 'red',
            default       => 'gray',
        };
    }

    public function getRiskColorAttribute(): string
    {
        return match($this->risk_level) {
            'low'    => 'green',
            'medium' => 'amber',
            'high'   => 'red',
            default  => 'gray',
        };
    }
}
