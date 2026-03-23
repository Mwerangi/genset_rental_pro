<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(
        int $userId,
        string $action,
        string $description,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        static::create([
            'user_id'     => $userId,
            'action'      => $action,
            'description' => $description,
            'model_type'  => $modelType,
            'model_id'    => $modelId,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => request()?->ip(),
            'user_agent'  => request()?->userAgent(),
            'created_at'  => now(),
        ]);
    }
}
