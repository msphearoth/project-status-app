@props(['title' => null, 'description' => null, 'icon' => null])

{{-- A white panel with an optional header (icon, title, description and an "actions" slot). --}}
<section {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-900/5 dark:ring-white/10']) }}>
    @if ($title)
        <div class="flex flex-wrap items-start justify-between gap-4 px-5 py-4 sm:px-6 border-b border-gray-100 dark:border-gray-700">
            <div class="flex min-w-0 items-start gap-3">
                @if ($icon)
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <x-icon :name="$icon" class="h-5 w-5" />
                    </span>
                @endif
                <div class="min-w-0">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
                    @if ($description)
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
                    @endif
                </div>
            </div>

            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    {{ $slot }}
</section>
