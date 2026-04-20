<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CompanySetting extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'vat_rate'               => 'decimal:2',
        'payment_terms_days'     => 'integer',
        'fx_clearing_account_id' => 'integer',
    ];

    /**
     * Return the single settings row, creating it with defaults if it doesn't exist.
     */
    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'company_name'       => 'Milele Power',
                'country'            => 'Tanzania',
                'vat_rate'           => 18.00,
                'default_currency'   => 'TZS',
                'invoice_prefix'     => 'INV',
                'quotation_prefix'   => 'QT',
                'payment_terms_days' => 30,
                'primary_color'      => '#dc2626',
                'secondary_color'    => '#1f2937',
            ]
        );
    }

    /**
     * Convenience static getter: CompanySetting::get('company_name').
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::current()->{$key} ?? $default;
    }

    /**
     * URL of the company logo or null if not set.
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::url($this->logo_path) : null;
    }

    /**
     * URL of the company stamp or null if not set.
     */
    public function getStampUrlAttribute(): ?string
    {
        return $this->stamp_path ? Storage::url($this->stamp_path) : null;
    }

    /**
     * Single-line full address string.
     */
    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->district,
            $this->region,
            $this->country,
        ])->filter()->implode(', ');
    }

    /**
     * Full postal address block (multi-line) for use in PDF headers.
     */
    public function getAddressBlockAttribute(): string
    {
        $lines = [];
        if ($this->address_line1) $lines[] = $this->address_line1;
        if ($this->address_line2) $lines[] = $this->address_line2;

        $cityLine = collect([$this->city, $this->district, $this->region])->filter()->implode(', ');
        if ($cityLine) $lines[] = $cityLine;

        if ($this->country) $lines[] = $this->country;
        if ($this->po_box)  $lines[] = 'P.O. Box ' . $this->po_box;

        return implode("\n", $lines);
    }

    /**
     * Return all bank details as a formatted text block.
     */
    public function getBankDetailsBlockAttribute(): string
    {
        $lines = [];
        if ($this->bank_name)           $lines[] = 'Bank: ' . $this->bank_name;
        if ($this->bank_branch_name)    $lines[] = 'Branch: ' . $this->bank_branch_name;
        if ($this->bank_account_name)   $lines[] = 'Account Name: ' . $this->bank_account_name;
        if ($this->bank_account_number) $lines[] = 'Account No.: ' . $this->bank_account_number;
        if ($this->bank_swift_code)     $lines[] = 'SWIFT/BIC: ' . $this->bank_swift_code;
        return implode("\n", $lines);
    }

    public function fxClearingAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'fx_clearing_account_id');
    }
}
