@props(['name', 'size' => 'md'])

{{-- Round initials badge for a person, coloured consistently from their name. --}}
@php
$initials = collect(preg_split('/\s+/u', trim($name)))
    ->filter()
    ->take(2)
    ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
    ->join('');
$palette = [
    'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300',
    'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300',
    'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
    'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
    'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
    'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300',
];
$colorClasses = $palette[crc32($name) % count($palette)];
$sizeClasses = match ($size) {
    'sm' => 'h-6 w-6 text-[10px]',
    'lg' => 'h-10 w-10 text-sm',
    default => 'h-8 w-8 text-xs',
};
@endphp

<span aria-hidden="true" {{ $attributes->merge(['class' => "inline-flex shrink-0 items-center justify-center rounded-full font-semibold {$sizeClasses} {$colorClasses}"]) }}>{{ $initials }}</span>
