<x-app-layout>
    <x-slot name="header">
        <x-page-heading :title="__('New Project')" :description="__('Register a new project request. It starts as Pending until it is assigned.')" :back="route('projects.index')" />
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('projects.store') }}">
                @csrf

                @include('projects._form')

                <div class="sticky bottom-0 z-10 mt-8 -mx-4 border-t border-gray-200 bg-white/90 px-4 py-4 backdrop-blur dark:border-gray-700 dark:bg-gray-800/90 sm:mx-0 sm:rounded-xl sm:border sm:shadow-lg">
                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-end">
                        <x-button-link :href="route('projects.index')">
                            {{ __('Cancel') }}
                        </x-button-link>
                        <x-primary-button>
                            <x-icon name="check" class="h-4 w-4" />
                            {{ __('Create Project') }}
                        </x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
