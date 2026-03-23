<?php

namespace App\Models;

use App\Services\PermissionService;
use App\Models\Role as RoleModel;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'department',
        'position',
        'status',
        'last_login_at',
        'last_login_ip',
        'login_attempts',
        'locked_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'last_login_at'     => 'datetime',
            'locked_until'      => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────

    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class);
    }

    // ── Helpers ────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Check if this user has one of the given roles.
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Check if this user has the given permission (uses cached DB lookup).
     * Super admin always returns true.
     */
    public function hasPermission(string $permission): bool
    {
        return PermissionService::can($this, $permission);
    }

    public function getRoleLabelAttribute(): string
    {
        return RoleModel::labelFor($this->role ?? '');
    }

    public function getRoleBadgeColorAttribute(): string
    {
        return RoleModel::badgeColorFor($this->role ?? '');
    }

    public static function roles(): array
    {
        return RoleModel::asKeyLabel();
    }
}
