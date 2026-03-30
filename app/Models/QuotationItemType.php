<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class QuotationItemType extends Model
{
    protected $fillable = [
        'key',
        'label',
        'is_rental',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_rental'  => 'boolean',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope: only active types, ordered by sort_order.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
