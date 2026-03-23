<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = AppNotification::forUser(auth()->id());
        $unreadCount   = AppNotification::unreadFor(auth()->id())->count();

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markRead(AppNotification $notification)
    {
        // Ensure user can only mark their own or broadcast notifications
        if ($notification->user_id && $notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markRead();

        if (request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }

    public function markAllRead()
    {
        AppNotification::where('is_read', false)
            ->where(fn($q) => $q->where('user_id', auth()->id())->orWhereNull('user_id'))
            ->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(AppNotification $notification)
    {
        if ($notification->user_id && $notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->delete();

        return back();
    }

    /**
     * JSON endpoint: count of unread notifications for the current user.
     */
    public function unreadCount()
    {
        $count = AppNotification::unreadFor(auth()->id())->count();
        return response()->json(['count' => $count]);
    }
}
