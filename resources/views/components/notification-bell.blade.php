@php
    $unreadCount = Auth::user()->unreadNotifications()->count();
    $notifications = Auth::user()->unreadNotifications()->latest()->take(10)->get();
@endphp

<x-dropdown align="right" width="w-80">
    <x-slot name="trigger">
        <button type="button" class="relative inline-flex items-center p-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150" aria-label="{{ __('Notifications') }}">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>

            @if ($unreadCount > 0)
                <span class="absolute top-0 end-0 inline-flex items-center justify-center min-w-5 h-5 px-1 text-xs font-semibold text-white bg-red-600 rounded-full">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @endif
        </button>
    </x-slot>

    <x-slot name="content">
        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 dark:border-gray-600">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Notifications') }}</span>

            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                        {{ __('Mark all as read') }}
                    </button>
                </form>
            @endif
        </div>

        @forelse ($notifications as $notification)
            <a href="{{ route('notifications.open', $notification->id) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
                <div>
                    {{ __(':name assigned project :code to you.', [
                        'name' => $notification->data['assigned_by_name'],
                        'code' => $notification->data['project_code'],
                    ]) }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $notification->created_at->diffForHumans() }}</div>
            </a>
        @empty
            <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ __('No new notifications.') }}</div>
        @endforelse
    </x-slot>
</x-dropdown>
