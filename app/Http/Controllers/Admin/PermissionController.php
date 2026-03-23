<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Show the role permissions management page.
     */
    public function index(Request $request)
    {
        $permissions   = Permission::orderBy('module')->orderBy('sort_order')->get()->groupBy('module');
        $roles         = User::roles();
        $selectedRole  = $request->get('role', 'admin');

        // Fall back to 'admin' if the requested role doesn't exist
        if (! array_key_exists($selectedRole, $roles)) {
            $selectedRole = 'admin';
        }

        // Build role → permission name array for every role (for quick JS/blade access)
        $rolePermissions = [];
        foreach (array_keys($roles) as $role) {
            $rolePermissions[$role] = RolePermission::where('role', $role)
                ->pluck('permission_name')
                ->toArray();
        }

        return view('admin.permissions.index', compact('permissions', 'roles', 'selectedRole', 'rolePermissions'));
    }

    /**
     * Update all permissions for a specific role.
     */
    public function update(Request $request, string $role)
    {
        // Validate the role exists
        abort_unless(array_key_exists($role, User::roles()), 404);

        // Super admin cannot have permissions modified — they bypass all checks anyway
        abort_if($role === 'super_admin', 403, 'Super Admin permissions cannot be modified.');

        $submitted      = $request->input('permissions', []);
        $validNames     = Permission::pluck('name')->toArray();
        $newPermissions = array_values(array_intersect($submitted, $validNames));

        // Replace all permissions for this role atomically
        RolePermission::where('role', $role)->delete();

        foreach ($newPermissions as $perm) {
            RolePermission::create(['role' => $role, 'permission_name' => $perm]);
        }

        // Invalidate cache for this role
        PermissionService::clearCache($role);

        $roleLabel = User::roles()[$role];

        return redirect()
            ->route('admin.permissions.index', ['role' => $role])
            ->with('success', "{$roleLabel} permissions updated successfully.");
    }
}
