<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id', 'type', 'title', 'body', 'link',
        'icon', 'is_read', 'read_at', 'created_by',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a notification for a specific user (or broadcast if user_id is null).
     */
    public static function notify(
        ?int $userId,
        string $type,
        string $title,
        ?string $body = null,
        ?string $link = null,
        ?string $icon = null
    ): self {
        return static::create([
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'body'       => $body,
            'link'       => $link,
            'icon'       => $icon,
            'is_read'    => false,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Return unread notifications for a user (includes broadcasts).
     */
    public static function unreadFor(int $userId)
    {
        return static::where('is_read', false)
            ->where(fn($q) => $q->where('user_id', $userId)->orWhereNull('user_id'))
            ->latest()
            ->get();
    }

    /**
     * Return all notifications for a user (includes broadcasts).
     */
    public static function forUser(int $userId)
    {
        return static::where(fn($q) => $q->where('user_id', $userId)->orWhereNull('user_id'))
            ->latest()
            ->paginate(30);
    }

    public function markRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }
}
