@props(['variant' => 'secondary'])

{{-- A link styled like x-primary-button or x-secondary-button. --}}
@php
$variantClasses = match ($variant) {
    'primary' => 'bg-indigo-600 text-white shadow-sm hover:bg-indigo-500 active:bg-indigo-700',
    default => 'bg-white text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-600 dark:hover:bg-gray-700',
};
@endphp

<a {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800 transition ease-in-out duration-150 '.$variantClasses]) }}>
    {{ $slot }}
</a>
