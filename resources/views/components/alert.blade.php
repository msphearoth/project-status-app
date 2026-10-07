@props(['type' => 'success'])

@php
[$typeClasses, $icon] = match ($type) {
    'error' => ['bg-red-50 text-red-800 ring-red-600/20 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30', 'x-circle'],
    'info' => ['bg-blue-50 text-blue-800 ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30', 'information-circle'],
    default => ['bg-green-50 text-green-800 ring-green-600/20 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30', 'check-circle'],
};
@endphp

<div role="{{ $type === 'error' ? 'alert' : 'status' }}" {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl p-4 text-sm ring-1 ring-inset '.$typeClasses]) }}>
    <x-icon :name="$icon" class="h-5 w-5 shrink-0" />
    <div class="min-w-0 space-y-1">{{ $slot }}</div>
</div>
