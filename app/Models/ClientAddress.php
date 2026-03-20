<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAddress extends Model
{
    protected $fillable = [
        'client_id',
        'type',
        'label',
        'street_address',
        'city',
        'region',
        'country',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'billing' => 'blue',
            'service' => 'green',
            'office'  => 'purple',
            default   => 'gray',
        };
    }
}
