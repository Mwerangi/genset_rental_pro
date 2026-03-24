<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'supplier_number',
        'name',
        'category',
        'contact_person',
        'phone',
        'phone_alt',
        'email',
        'website',
        'address',
        'city',
        'country',
        'tin_number',
        'vrn_number',
        'payment_terms',
        'currency',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            if (empty($supplier->supplier_number)) {
                $supplier->supplier_number = static::generateSupplierNumber();
            }
        });
    }

    public static function generateSupplierNumber(): string
    {
        $year = date('Y');
        $prefix = 'SUP-' . $year . '-';
        $last = static::where('supplier_number', 'like', $prefix . '%')
            ->orderBy('supplier_number', 'desc')->first();
        $n = $last ? ((int) substr($last->supplier_number, -4)) + 1 : 1;
        return $prefix . str_pad($n, 4, '0', STR_PAD_LEFT);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }
}
