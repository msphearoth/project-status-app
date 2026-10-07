<div class="inline-flex items-center rounded-lg bg-gray-100 dark:bg-gray-700/50 p-0.5 text-xs font-medium">
    @foreach (config('app.supported_locales') as $locale)
        <a href="{{ route('locale.switch', $locale) }}"
           @if (app()->getLocale() === $locale) aria-current="true" @endif
           class="rounded-md px-2.5 py-1 transition {{ app()->getLocale() === $locale ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
            {{ $locale === 'km' ? 'ខ្មែរ' : strtoupper($locale) }}
        </a>
    @endforeach
</div>
