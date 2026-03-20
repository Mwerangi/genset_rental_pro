<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientContact extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'position',
        'email',
        'phone',
        'is_primary',
        'can_authorize_bookings',
        'can_receive_invoices',
    ];

    protected $casts = [
        'is_primary'             => 'boolean',
        'can_authorize_bookings' => 'boolean',
        'can_receive_invoices'   => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
