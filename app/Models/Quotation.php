<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quotation extends Model
{
    protected $fillable = [
        'quotation_number',
        'quote_request_id',
        'client_id',
        'booking_id',
        'status',
        'valid_until',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'total_amount',
        'currency',
        'exchange_rate_to_tzs',
        'payment_terms',
        'terms_conditions',
        'notes',
        'created_by',
        'sent_at',
        'viewed_at',
        'accepted_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'exchange_rate_to_tzs' => 'decimal:4',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Boot method to generate quotation number automatically
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quotation) {
            if (empty($quotation->quotation_number)) {
                $quotation->quotation_number = static::generateQuotationNumber();
            }
        });
    }

    /**
     * Generate unique quotation number (QT-YYYY-0001)
     */
    public static function generateQuotationNumber(): string
    {
        $year = date('Y');
        $prefix = 'QT-' . $year . '-';
        
        $lastQuotation = static::where('quotation_number', 'like', $prefix . '%')
            ->orderBy('quotation_number', 'desc')
            ->first();

        if ($lastQuotation) {
            $lastNumber = (int) substr($lastQuotation->quotation_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }

    /**
     * Scopes
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', '!=', 'accepted')
                     ->where('valid_until', '<', now());
    }

    /**
     * Helper methods
     */
    public function calculateTotals()
    {
        $this->subtotal = $this->items()->sum('subtotal');
        $this->vat_amount = $this->subtotal * ($this->vat_rate / 100);
        $this->total_amount = $this->subtotal + $this->vat_amount;
        $this->save();
    }

    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsViewed()
    {
        if ($this->status === 'sent' && !$this->viewed_at) {
            $this->update([
                'status' => 'viewed',
                'viewed_at' => now(),
            ]);
        }
    }

    public function markAsAccepted()
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    public function markAsRejected(?string $reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function isExpired(): bool
    {
        return $this->status !== 'accepted' && $this->valid_until < now();
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'sent', 'viewed', 'accepted']);
    }

    public function canBeConverted(): bool
    {
        return $this->status === 'accepted' && !$this->booking_id;
    }

    /**
     * Attributes
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'default',
            'sent' => 'info',
            'viewed' => 'info',
            'accepted' => 'success',
            'rejected' => 'danger',
            'expired' => 'warning',
            default => 'default',
        };
    }

    public function getFormattedTotalAttribute(): string
    {
        return $this->currencySymbol() . ' ' . number_format($this->total_amount, 2);
    }

    public function currencySymbol(): string
    {
        return $this->currency === 'USD' ? 'USD' : 'TZS';
    }

    public function formatAmount(float $amount, int $decimals = 2): string
    {
        return $this->currencySymbol() . ' ' . number_format($amount, $decimals);
    }

    /** Total converted to TZS (for accounting/reporting) */
    public function totalInTzs(): float
    {
        return round((float) $this->total_amount * (float) $this->exchange_rate_to_tzs, 2);
    }
}
