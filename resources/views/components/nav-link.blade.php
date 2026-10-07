@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-indigo-700 bg-indigo-50 dark:text-indigo-300 dark:bg-indigo-500/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition duration-150 ease-in-out'
            : 'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 hover:bg-gray-100 dark:hover:text-gray-100 dark:hover:bg-gray-700/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
