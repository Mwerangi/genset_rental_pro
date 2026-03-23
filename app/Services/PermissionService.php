<?php

namespace App\Services;

use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    /**
     * Get all permission names for a given role (cached).
     */
    public static function getForRole(string $role): array
    {
        return Cache::remember("role_permissions.{$role}", 3600, function () use ($role) {
            return RolePermission::where('role', $role)->pluck('permission_name')->toArray();
        });
    }

    /**
     * Check whether the given user has a specific permission.
     * Super admin always returns true regardless of DB state.
     */
    public static function can(User $user, string $permission): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        return in_array($permission, static::getForRole($user->role), true);
    }

    /**
     * Invalidate the cached permissions for a single role.
     */
    public static function clearCache(string $role): void
    {
        Cache::forget("role_permissions.{$role}");
    }

    /**
     * Invalidate the cached permissions for all roles.
     */
    public static function clearAllCache(): void
    {
        foreach (array_keys(User::roles()) as $role) {
            static::clearCache($role);
        }
    }
}
