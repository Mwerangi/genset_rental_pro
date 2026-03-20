<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNote extends Model
{
    protected $fillable = [
        'cn_number', 'invoice_id', 'client_id', 'reason',
        'amount', 'vat_amount', 'total_amount', 'status',
        'issued_date', 'journal_entry_id', 'issued_by', 'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'vat_amount'   => 'decimal:2',
        'total_amount' => 'decimal:2',
        'issued_date'  => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cn) {
            if (empty($cn->cn_number)) {
                $cn->cn_number = static::generateCnNumber();
            }
            if (empty($cn->total_amount)) {
                $cn->total_amount = (float) $cn->amount + (float) ($cn->vat_amount ?? 0);
            }
        });
    }

    public static function generateCnNumber(): string
    {
        $year = date('Y');
        $prefix = 'CN-' . $year . '-';
        $last = static::where('cn_number', 'like', $prefix . '%')
            ->orderBy('cn_number', 'desc')->first();
        $n = $last ? ((int) substr($last->cn_number, -4)) + 1 : 1;
        return $prefix . str_pad($n, 4, '0', STR_PAD_LEFT);
    }

    // ─── Relationships ───────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    // ─── Accessors ───────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'   => 'Draft',
            'issued'  => 'Issued',
            'applied' => 'Applied',
            'voided'  => 'Voided',
            default   => ucfirst($this->status),
        };
    }

    public function getStatusStyleAttribute(): string
    {
        return match ($this->status) {
            'draft'   => 'background:#f3f4f6;color:#374151;',
            'issued'  => 'background:#dbeafe;color:#1e40af;',
            'applied' => 'background:#dcfce7;color:#166534;',
            'voided'  => 'background:#fee2e2;color:#991b1b;',
            default   => 'background:#f3f4f6;color:#374151;',
        };
    }
}
