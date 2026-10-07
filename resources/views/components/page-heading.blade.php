@props(['title', 'description' => null, 'back' => null])

{{-- Page title row for the layout header: optional back link, title, "badge" slot, description and action buttons. --}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        @if ($back)
            <a href="{{ $back }}" class="mb-1 inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <x-icon name="arrow-left" class="h-4 w-4" />
                {{ __('Back') }}
            </a>
        @endif
        <h2 class="flex flex-wrap items-center gap-x-3 gap-y-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
            <span class="break-all">{{ $title }}</span>
            {{ $badge ?? '' }}
        </h2>
        @if ($description)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
        @endif
    </div>

    @if ($slot->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2 sm:shrink-0">{{ $slot }}</div>
    @endif
</div>
