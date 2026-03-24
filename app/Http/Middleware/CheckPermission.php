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

        // Each argument is AND-checked. Within a single argument, | means OR (any one satisfies).
        // e.g. permission:view_accounting|view_cash_requests,approve_something
        //   = (view_accounting OR view_cash_requests) AND (approve_something)
        foreach ($permissions as $permGroup) {
            $anyOf  = array_map('trim', explode('|', $permGroup));
            $passed = false;
            foreach ($anyOf as $perm) {
                if (PermissionService::can($user, $perm)) {
                    $passed = true;
                    break;
                }
            }
            if (! $passed) {
                abort(403, 'You do not have permission to access this page.');
            }
        }

        return $next($request);
    }
}
