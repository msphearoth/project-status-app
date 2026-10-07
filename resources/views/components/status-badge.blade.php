@props(['status'])

{{-- Pill showing a project status with a coloured dot. --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap '.$status->badgeClass()]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>
    {{ $status->label() }}
</span>
