<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::latest();

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%")
                  ->orWhere('department', 'like', "%$s%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        $stats = [
            'total'    => User::count(),
            'active'   => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
            'locked'   => User::where('locked_until', '>', now())->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function create()
    {
        $roles = User::roles();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|max:50',
            'role'       => 'required|in:' . implode(',', array_keys(User::roles())),
            'department' => 'nullable|string|max:100',
            'position'   => 'nullable|string|max:100',
            'status'     => 'required|in:active,inactive',
            'password'   => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'] ?? null,
            'role'       => $validated['role'],
            'department' => $validated['department'] ?? null,
            'position'   => $validated['position'] ?? null,
            'status'     => $validated['status'],
            'password'   => Hash::make($validated['password']),
        ]);

        UserActivityLog::record(
            auth()->id(),
            'created',
            "Created user {$user->name} ({$user->email})",
            User::class,
            $user->id
        );

        return redirect()->route('admin.users.show', $user)
            ->with('success', "User {$user->name} created successfully.");
    }

    public function show(User $user)
    {
        $user->load('activityLogs');
        $recentActivity = $user->activityLogs()->latest('created_at')->take(20)->get();
        return view('admin.users.show', compact('user', 'recentActivity'));
    }

    public function edit(User $user)
    {
        $roles = User::roles();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'phone'      => 'nullable|string|max:50',
            'role'       => 'required|in:' . implode(',', array_keys(User::roles())),
            'department' => 'nullable|string|max:100',
            'position'   => 'nullable|string|max:100',
            'status'     => 'required|in:active,inactive,suspended',
        ]);

        $oldValues = $user->only(['name', 'email', 'role', 'status']);
        $user->update($validated);

        UserActivityLog::record(
            auth()->id(),
            'updated',
            "Updated user {$user->name}",
            User::class,
            $user->id,
            $oldValues,
            $user->only(['name', 'email', 'role', 'status'])
        );

        return redirect()->route('admin.users.show', $user)
            ->with('success', "User {$user->name} updated.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->update([
            'password'       => Hash::make($validated['password']),
            'login_attempts' => 0,
            'locked_until'   => null,
        ]);

        UserActivityLog::record(
            auth()->id(),
            'password_reset',
            "Reset password for {$user->name}",
            User::class,
            $user->id
        );

        return back()->with('success', 'Password reset successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own status.');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        UserActivityLog::record(
            auth()->id(),
            'status_changed',
            "Changed {$user->name} status to {$newStatus}",
            User::class,
            $user->id
        );

        return back()->with('success', "User {$user->name} is now {$newStatus}.");
    }

    public function unlock(User $user)
    {
        $user->update(['login_attempts' => 0, 'locked_until' => null]);

        UserActivityLog::record(
            auth()->id(),
            'unlocked',
            "Unlocked account for {$user->name}",
            User::class,
            $user->id
        );

        return back()->with('success', "Account unlocked for {$user->name}.");
    }

    public function activityLog(User $user)
    {
        $logs = $user->activityLogs()->latest('created_at')->paginate(50);
        return view('admin.users.activity-log', compact('user', 'logs'));
    }
}
