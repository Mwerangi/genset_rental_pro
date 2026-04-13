<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'booking_number',
        'client_id',
        'quote_request_id',
        'quotation_id',
        'genset_id',
        'invoice_id',
        'status',
        'genset_type',
        'rental_start_date',
        'rental_end_date',
        'rental_duration_days',
        'drop_on_location',
        'drop_off_location',
        'destination',
        'total_amount',
        'currency',
        'exchange_rate_to_tzs',
        'notes',
        'is_historical',
        'customer_name',
        'customer_email',
        'customer_phone',
        'company_name',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'rental_start_date'   => 'date',
        'rental_end_date'     => 'date',
        'is_historical'       => 'boolean',
        'approved_at'         => 'datetime',
        'cancelled_at'        => 'datetime',
        'total_amount'        => 'decimal:2',
        'exchange_rate_to_tzs'=> 'decimal:4',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_number)) {
                $booking->booking_number = static::generateBookingNumber();
            }
        });
    }

    public static function generateBookingNumber(): string
    {
        $year = date('Y');
        $prefix = 'BK-' . $year . '-';

        $last = static::where('booking_number', 'like', $prefix . '%')
            ->orderBy('booking_number', 'desc')
            ->first();

        $newNumber = $last ? ((int) substr($last->booking_number, -4)) + 1 : 1;

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function genset(): BelongsTo
    {
        return $this->belongsTo(Genset::class);
    }

    public function gensets(): BelongsToMany
    {
        return $this->belongsToMany(Genset::class, 'booking_genset')->withTimestamps();
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function activatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function invoicedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invoiced_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    // Computed attributes

    public function getFormattedTotalAttribute(): string
    {
        return ($this->currency === 'USD' ? 'USD' : 'TZS') . ' ' . number_format($this->total_amount, 0);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'created'   => 'blue',
            'approved'  => 'green',
            'active'    => 'teal',
            'returned'  => 'purple',
            'invoiced'  => 'orange',
            'paid'      => 'green',
            'cancelled' => 'red',
            'rejected'  => 'red',
            default     => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'created'   => 'Pending Approval',
            'approved'  => 'Approved',
            'active'    => 'Active (On Rent)',
            'returned'  => 'Returned',
            'invoiced'  => 'Invoiced',
            'paid'      => 'Paid',
            'cancelled' => 'Cancelled',
            'rejected'  => 'Rejected',
            default     => ucfirst($this->status),
        };
    }

    // Actions

    public function canBeApproved(): bool
    {
        return $this->status === 'created';
    }

    public function approve(int $userId): void
    {
        $this->update([
            'status'      => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function canBeRejected(): bool
    {
        return $this->status === 'created';
    }

    public function reject(int $userId, ?string $reason = null): void
    {
        $this->update([
            'status'              => 'rejected',
            'cancelled_by'        => $userId,
            'cancelled_at'        => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function canBeActivated(): bool
    {
        return $this->status === 'approved';
    }

    public function activate(int $userId, ?int $gensetId = null): void
    {
        $data = [
            'status'       => 'active',
            'activated_by' => $userId,
            'activated_at' => now(),
        ];

        if ($gensetId) {
            $data['genset_id'] = $gensetId;
        }

        $this->update($data);

        if ($gensetId) {
            \App\Models\Genset::where('id', $gensetId)->update(['status' => 'rented']);
        }
    }

    public function canBeMarkedReturned(): bool
    {
        return $this->status === 'active';
    }

    public function markReturned(int $userId): void
    {
        $gensetId = $this->genset_id;

        $this->update([
            'status'      => 'returned',
            'returned_by' => $userId,
            'returned_at' => now(),
        ]);

        if ($gensetId) {
            // Genset goes into maintenance after every rental — must be cleared by the maintenance module before re-hire
            \App\Models\Genset::where('id', $gensetId)->update(['status' => 'maintenance']);
        }
    }

    public function canBeInvoiced(): bool
    {
        return $this->status === 'returned';
    }

    public function markInvoiced(int $userId, ?string $invoiceNumber = null): void
    {
        $this->update([
            'status'         => 'invoiced',
            'invoiced_by'    => $userId,
            'invoiced_at'    => now(),
            'invoice_number' => $invoiceNumber,
        ]);
    }

    public function canBeMarkedPaid(): bool
    {
        return $this->status === 'invoiced';
    }

    public function markPaid(int $userId, ?string $paymentReference = null): void
    {
        $this->update([
            'status'            => 'paid',
            'paid_by'           => $userId,
            'paid_at'           => now(),
            'payment_reference' => $paymentReference,
        ]);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['created', 'approved', 'active']);
    }

    public function cancel(int $userId, ?string $reason = null): void
    {
        $this->update([
            'status'              => 'cancelled',
            'cancelled_by'        => $userId,
            'cancelled_at'        => now(),
            'cancellation_reason' => $reason,
        ]);
    }
}
