<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'invoice_type',
        'converted_from_id',
        'booking_id',
        'client_id',
        'quotation_id',
        'status',
        'issue_date',
        'due_date',
        'subtotal',
        'discount_amount',
        'discount_reason',
        'is_zero_rated',
        'vat_rate',
        'vat_amount',
        'currency',
        'exchange_rate_to_tzs',
        'total_amount',
        'amount_paid',
        'payment_terms',
        'terms_conditions',
        'notes',
        'created_by',
        'sent_at',
        'void_at',
        'void_reason',
    ];

    protected $casts = [
        'issue_date'          => 'date',
        'due_date'            => 'date',
        'is_zero_rated'       => 'boolean',
        'subtotal'            => 'decimal:2',
        'discount_amount'     => 'decimal:2',
        'vat_rate'            => 'decimal:2',
        'vat_amount'          => 'decimal:2',
        'exchange_rate_to_tzs'=> 'decimal:4',
        'total_amount'        => 'decimal:2',
        'amount_paid'         => 'decimal:2',
        'sent_at'             => 'datetime',
        'void_at'             => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $prefix = 'INV-' . $year . '-';

        $last = static::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        $newNumber = $last ? ((int) substr($last->invoice_number, -4)) + 1 : 1;

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function generateProformaNumber(): string
    {
        $year = date('Y');
        $prefix = 'PRO-' . $year . '-';

        $last = static::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        $newNumber = $last ? ((int) substr($last->invoice_number, -4)) + 1 : 1;

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function isProforma(): bool
    {
        return $this->invoice_type === 'proforma';
    }

    // ─── Relationships ──────────────────────────────────────────────

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class)->orderBy('payment_date');
    }

    // ─── Computed Attributes ─────────────────────────────────────────

    public function getBalanceDueAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->amount_paid);
    }

    public function getPaymentProgressAttribute(): int
    {
        if ($this->total_amount <= 0) return 100;
        return (int) min(100, round(($this->amount_paid / $this->total_amount) * 100));
    }

    public function getIsOverdueAttribute(): bool
    {
        return !in_array($this->status, ['paid', 'void', 'declined'])
            && $this->due_date
            && now()->isAfter($this->due_date);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'          => 'Draft',
            'sent'           => 'Sent — Awaiting Payment',
            'partially_paid' => 'Partially Paid',
            'paid'           => 'Paid',
            'void'           => 'Void',
            'declined'       => 'Declined',
            'disputed'       => 'Disputed',
            'written_off'    => 'Written Off',
            default          => ucfirst($this->status),
        };
    }

    public function getStatusStyleAttribute(): string
    {
        return match ($this->status) {
            'draft'          => 'background:#f3f4f6;color:#374151;',
            'sent'           => 'background:#dbeafe;color:#1e40af;',
            'partially_paid' => 'background:#fef9c3;color:#854d0e;',
            'paid'           => 'background:#dcfce7;color:#166534;',
            'void'           => 'background:#fee2e2;color:#991b1b;',
            'declined'       => 'background:#fee2e2;color:#7f1d1d;',
            'disputed'       => 'background:#fce7f3;color:#9d174d;',
            'written_off'    => 'background:#f3f4f6;color:#6b7280;',
            default          => 'background:#f3f4f6;color:#374151;',
        };
    }

    public function getIsEditableAttribute(): bool
    {
        return in_array($this->status, ['draft', 'sent', 'partially_paid', 'disputed']);
    }

    // ─── Currency Helpers ─────────────────────────────────────────────

    public function currencySymbol(): string
    {
        return $this->currency === 'USD' ? 'USD' : 'TZS';
    }

    public function formatAmount(float $amount, int $decimals = 2): string
    {
        return $this->currencySymbol() . ' ' . number_format($amount, $decimals);
    }

    /** Total converted to TZS for journal entries and reporting */
    public function totalInTzs(): float
    {
        return round((float) $this->total_amount * (float) $this->exchange_rate_to_tzs, 2);
    }

    public function amountInTzs(float $amount): float
    {
        return round($amount * (float) $this->exchange_rate_to_tzs, 2);
    }

    // ─── Actions ─────────────────────────────────────────────────────

    public function markSent(): void
    {
        $this->update(['status' => 'sent', 'sent_at' => now()]);
    }

    public function voidInvoice(string $reason = ''): void
    {
        $this->update([
            'status'      => 'void',
            'void_at'     => now(),
            'void_reason' => $reason,
        ]);
    }

    public function markDisputed(string $reason = ''): void
    {
        $notes = $this->notes;
        if ($reason) {
            $notes = trim(($notes ?? '') . "\n[Disputed " . now()->format('d M Y') . '] ' . $reason);
        }
        $this->update(['status' => 'disputed', 'notes' => $notes]);
    }

    public function writeOff(): void
    {
        $this->update(['status' => 'written_off']);
    }

    /**
     * Recompute subtotal/VAT/total from line items, then recalculate payment progress.
     * Call this after any item add/edit/delete or discount change.
     */
    public function recalculateTotals(): void
    {
        $this->loadMissing('items');
        $itemsSubtotal = (float) $this->items()->sum('subtotal');
        $discount      = (float) ($this->discount_amount ?? 0);
        $afterDiscount = max(0, $itemsSubtotal - $discount);
        $vatAmount     = $this->is_zero_rated ? 0 : round($afterDiscount * ((float) $this->vat_rate / 100), 2);
        $totalAmount   = $afterDiscount + $vatAmount;

        $this->update([
            'subtotal'     => $itemsSubtotal,
            'vat_amount'   => $vatAmount,
            'total_amount' => $totalAmount,
        ]);

        $this->refresh();
        $this->recalculatePayments();
    }

    /**
     * After recording/deleting a payment, recalculate amount_paid and update status.
     * Also closes the booking when fully paid.
     */
    public function recalculatePayments(): void
    {
        // Only count non-reversed payments
        $totalPaid = $this->payments()->where('is_reversed', false)->sum('amount');

        $status = $this->status;
        if (!in_array($status, ['void', 'declined', 'written_off'])) {
            if ($totalPaid >= $this->total_amount && $this->total_amount > 0) {
                $status = 'paid';
            } elseif ($totalPaid > 0) {
                $status = 'partially_paid';
            } elseif (in_array($status, ['partially_paid'])) {
                // all payments removed — revert to sent if it was sent
                $status = $this->sent_at ? 'sent' : 'draft';
            }
        }

        $this->update([
            'amount_paid' => $totalPaid,
            'status'      => $status,
        ]);

        // Sync booking status when fully paid
        if ($status === 'paid' && $this->booking_id) {
            Booking::where('id', $this->booking_id)
                ->whereNotIn('status', ['paid', 'cancelled', 'rejected'])
                ->update(['status' => 'paid', 'paid_at' => now()]);
        }
    }
}
