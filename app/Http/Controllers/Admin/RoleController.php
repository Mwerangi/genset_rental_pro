<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles       = Role::orderBy('sort_order')->get();
        $userCounts  = User::query()
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $permCounts = RolePermission::query()
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $colors = Role::availableColors();

        return view('admin.roles.index', compact('roles', 'userCounts', 'permCounts', 'colors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label'       => 'required|string|max:60',
            'key'         => 'nullable|string|max:60|alpha_dash',
            'description' => 'nullable|string|max:255',
            'badge_color' => 'required|string',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        // Generate key from label if not supplied
        $key = Str::snake(Str::slug($data['key'] ?? $data['label'], '_'));

        if (Role::where('key', $key)->exists()) {
            return back()->withErrors(['key' => "A role with key \"{$key}\" already exists."])->withInput();
        }

        $allowedColors = array_keys(Role::availableColors());
        abort_unless(in_array($data['badge_color'], $allowedColors, true), 422);

        Role::create([
            'key'         => $key,
            'label'       => $data['label'],
            'description' => $data['description'] ?? null,
            'badge_color' => $data['badge_color'],
            'sort_order'  => $data['sort_order'] ?? 99,
            'is_system'   => false,
        ]);

        Role::clearCache();

        return back()->with('success', "Role \"{$data['label']}\" created. Assign its permissions from the Permissions page.");
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'label'       => 'required|string|max:60',
            'description' => 'nullable|string|max:255',
            'badge_color' => 'required|string',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $allowedColors = array_keys(Role::availableColors());
        abort_unless(in_array($data['badge_color'], $allowedColors, true), 422);

        $role->update([
            'label'       => $data['label'],
            'description' => $data['description'] ?? null,
            'badge_color' => $data['badge_color'],
            'sort_order'  => $data['sort_order'] ?? $role->sort_order,
        ]);

        Role::clearCache();

        return back()->with('success', "Role \"{$role->label}\" updated.");
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->withErrors(['delete' => "System roles cannot be deleted."]);
        }

        $usersCount = User::where('role', $role->key)->count();
        if ($usersCount > 0) {
            return back()->withErrors(['delete' => "Cannot delete \"{$role->label}\" — {$usersCount} user(s) are assigned to it. Reassign them first."]);
        }

        // Remove all permissions for this role
        RolePermission::where('role', $role->key)->delete();
        PermissionService::clearCache($role->key);

        $label = $role->label;
        $role->delete();
        Role::clearCache();

        return back()->with('success', "Role \"{$label}\" deleted.");
    }
}
