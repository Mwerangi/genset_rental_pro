<?php

namespace App\Providers;

use App\Services\PermissionService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /**
         * @permission('view_bookings') ... @endpermission
         * Returns true if the authenticated user has ANY of the listed permissions.
         */
        Blade::if('permission', function (string ...$permissions): bool {
            if (! auth()->check()) {
                return false;
            }
            foreach ($permissions as $permission) {
                if (PermissionService::can(auth()->user(), $permission)) {
                    return true;
                }
            }
            return false;
        });

        /**
         * @role('admin', 'super_admin') ... @endrole
         * Returns true if the authenticated user has ANY of the listed roles.
         */
        Blade::if('role', function (string ...$roles): bool {
            if (! auth()->check()) {
                return false;
            }
            return auth()->user()->hasRole(...$roles);
        });
    }
}
