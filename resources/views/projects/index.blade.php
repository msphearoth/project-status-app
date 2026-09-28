<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Projects') }}
            </h2>

            <div class="flex items-center gap-3">
                @can('create', \App\Models\Project::class)
                    <a href="{{ route('projects.import.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                        {{ __('Import from Excel') }}
                    </a>
                @endcan

                @if ($projects->isNotEmpty())
                    <a href="{{ route('projects.export', request()->query()) }}"
                       class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                        {{ __('Export to Excel') }}
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 bg-green-100 dark:bg-green-800/30 text-green-800 dark:text-green-400 rounded-md">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('projects.index') }}" class="flex flex-wrap items-end gap-4">
                    <div>
                        <x-input-label for="search" :value="__('Search')" />
                        <x-text-input id="search" name="search" type="text" class="mt-1 block w-56"
                            value="{{ request('search') }}" placeholder="{{ __('Project code, work code, road...') }}" />
                    </div>

                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="mt-1 block w-48 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach (\App\Enums\ProjectStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="assignee_id" :value="__('Assignee')" />
                        <select id="assignee_id" name="assignee_id" class="mt-1 block w-48 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($assignees as $assignee)
                                <option value="{{ $assignee->id }}" @selected(request('assignee_id') == $assignee->id)>
                                    {{ $assignee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <x-secondary-button type="submit">{{ __('Filter') }}</x-secondary-button>

                    @if (request('search') || request('status') || request('assignee_id'))
                        <a href="{{ route('projects.index') }}" class="text-sm text-gray-500 dark:text-gray-400 underline">
                            {{ __('Reset') }}
                        </a>
                    @endif
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Project Code') }}</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Work Code') }}</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Assignee') }}</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Assigned On') }}</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Received Date') }}</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($projects as $project)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $project->project_code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $project->work_code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $project->status->badgeClass() }}">
                                            {{ $project->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $project->assignee?->name ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $project->assignee_id ? $project->latestAssignmentLog?->assignedDate()?->format('d-M-y') ?? '—' : '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $project->received_date->format('d-M-y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-end text-sm">
                                        <div x-data="{
                                                open: false,
                                                menuStyle: '',
                                                toggle() {
                                                    const rect = this.$refs.trigger.getBoundingClientRect();
                                                    this.menuStyle = `top: ${rect.bottom + 4}px; right: ${window.innerWidth - rect.right}px;`;
                                                    this.open = ! this.open;
                                                },
                                            }"
                                            x-on:click.outside="open = false"
                                            x-on:keydown.escape.window="open = false"
                                            x-on:scroll.window="open = false"
                                            x-on:resize.window="open = false"
                                            class="inline-block">
                                            <button type="button" x-ref="trigger" x-on:click="toggle()"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                                {{ __('Actions') }}
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>

                                            <div x-show="open" x-transition.opacity.duration.100ms x-bind:style="menuStyle" style="display: none;"
                                                class="fixed z-50 w-40 py-1 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 text-start">
                                                <x-dropdown-link :href="route('projects.show', $project)">
                                                    {{ __('View') }}
                                                </x-dropdown-link>
                                                @can('update', $project)
                                                    <x-dropdown-link :href="route('projects.edit', $project)">
                                                        {{ __('Edit') }}
                                                    </x-dropdown-link>
                                                @endcan
                                                @can('delete', $project)
                                                    <button type="button"
                                                        x-on:click="open = false; $dispatch('confirm-project-deletion', { url: @js(route('projects.destroy', $project)), code: @js($project->project_code) })"
                                                        class="block w-full px-4 py-2 text-start text-sm leading-5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-gray-800 focus:outline-none focus:bg-red-50 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
                                                        {{ __('Delete') }}
                                                    </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('No projects found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4">
                    {{ $projects->links() }}
                </div>
            </div>
        </div>
    </div>

    <div x-data="{ deleteUrl: '', projectCode: '' }"
        x-on:confirm-project-deletion.window="deleteUrl = $event.detail.url; projectCode = $event.detail.code; $dispatch('open-modal', 'confirm-project-deletion')">
        <x-modal name="confirm-project-deletion" focusable>
            <form method="POST" x-bind:action="deleteUrl" class="p-6">
                @csrf
                @method('DELETE')

                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('Are you sure you want to delete this project?') }}
                    <span class="font-semibold" x-text="projectCode"></span>
                </h2>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('This action cannot be undone. All assignment history for this project will also be deleted.') }}
                </p>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-danger-button class="ms-3">
                        {{ __('Delete') }}
                    </x-danger-button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>
