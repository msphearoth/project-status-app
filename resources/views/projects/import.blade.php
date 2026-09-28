<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Import Projects from Excel') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4 text-sm text-gray-700 dark:text-gray-300">
                <p>{{ __('Download the template, enter one project per row below the heading row, then upload the file. Imported projects are created as Pending.') }}</p>
                <p>{{ __('If any row has an error, nothing is imported. Fix the listed rows and upload the file again.') }}</p>

                <a href="{{ route('projects.import.template') }}"
                   class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ __('Download Template') }}
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('projects.import.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="file" :value="__('Excel File')" />
                        <input id="file" name="file" type="file" accept=".xlsx,.xls,.csv" required
                               class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300 file:me-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-gray-100 dark:file:bg-gray-700 file:text-gray-700 dark:file:text-gray-300" />

                        @if ($errors->has('file'))
                            <ul class="mt-2 space-y-1 text-sm text-red-600 dark:text-red-400 list-disc ps-5">
                                @foreach ($errors->get('file') as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('projects.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button>{{ __('Import') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
