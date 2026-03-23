<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $unreadCount }} unread</p>
        </div>
        @if($unreadCount > 0)
        <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
            @csrf
            <button type="submit" class="px-4 py-2 text-sm font-medium border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Mark all as read
            </button>
        </form>
        @endif
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        @if($notifications->isEmpty())
            <div class="px-6 py-16 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <p class="text-sm">You have no notifications.</p>
            </div>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach($notifications as $notif)
                <li class="flex items-start gap-4 px-5 py-4 {{ !$notif->is_read ? 'bg-red-50/40' : '' }} hover:bg-gray-50 transition-colors">
                    {{-- Icon --}}
                    <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5
                        {{ match($notif->type) {
                            'booking'     => 'bg-blue-100 text-blue-700',
                            'invoice'     => 'bg-green-100 text-green-700',
                            'maintenance' => 'bg-yellow-100 text-yellow-700',
                            'system'      => 'bg-gray-100 text-gray-600',
                            default       => 'bg-red-100 text-red-700',
                        } }}">
                        @if($notif->type === 'booking')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        @elseif($notif->type === 'invoice')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        @elseif($notif->type === 'maintenance')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-sm font-semibold text-gray-900 {{ !$notif->is_read ? '' : 'font-medium text-gray-700' }}">
                                    {{ $notif->title }}
                                    @if(!$notif->is_read)
                                        <span class="ml-1 w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                                    @endif
                                </p>
                                @if($notif->body)
                                <p class="text-sm text-gray-500 mt-0.5">{{ $notif->body }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if($notif->link)
                                    <a href="{{ $notif->link }}" class="text-xs text-red-600 hover:underline">View</a>
                                @endif
                                @if(!$notif->is_read)
                                <form method="POST" action="{{ route('admin.notifications.mark-read', $notif) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-gray-400 hover:text-gray-600">Mark read</button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.notifications.destroy', $notif) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-gray-300 hover:text-red-500">&times;</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>

            @if($notifications->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">{{ $notifications->links() }}</div>
            @endif
        @endif
    </div>
</x-admin-layout>
