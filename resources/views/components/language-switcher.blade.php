<div class="flex items-center space-x-1 text-sm">
    @foreach (config('app.supported_locales') as $locale)
        <a href="{{ route('locale.switch', $locale) }}"
           class="px-2 py-1 rounded-md {{ app()->getLocale() === $locale ? 'font-semibold text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
            {{ $locale === 'km' ? 'ខ្មែរ' : strtoupper($locale) }}
        </a>
        @if (!$loop->last)
            <span class="text-gray-300 dark:text-gray-600">|</span>
        @endif
    @endforeach
</div>
