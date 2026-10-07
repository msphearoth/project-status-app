@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-start text-base font-semibold text-indigo-700 bg-indigo-50 dark:text-indigo-300 dark:bg-indigo-500/10 focus:outline-none transition duration-150 ease-in-out'
            : 'flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 hover:bg-gray-100 dark:hover:text-gray-100 dark:hover:bg-gray-700/50 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700/50 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
