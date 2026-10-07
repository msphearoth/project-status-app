@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-indigo-500 dark:focus:ring-indigo-500 shadow-sm sm:text-sm disabled:cursor-not-allowed disabled:bg-gray-50 dark:disabled:bg-gray-800']) }}>
