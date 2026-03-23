<?php

namespace App\Http\Middleware;

use App\Services\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes: ->middleware('permission:view_bookings')
     *             or: ->middleware('permission:view_bookings,manage_bookings')
     *
     * Multiple permissions = ALL must be satisfied (AND logic).
     * To require ANY, use separate route groups.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Super admin bypasses all permission checks
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if (! PermissionService::can($user, $permission)) {
                abort(403, 'You do not have permission to access this page.');
            }
        }

        return $next($request);
    }
}
