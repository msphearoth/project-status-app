<x-app-layout>
    <x-slot name="header">
        <x-page-heading :title="__('Projects')" :description="__('Track, filter and assign every project in one place.')">
            @can('create', \App\Models\Project::class)
                <x-button-link :href="route('projects.import.create')">
                    <x-icon name="arrow-up-tray" class="h-4 w-4" />
                    {{ __('Import from Excel') }}
                </x-button-link>
            @endcan

            @if ($projects->isNotEmpty())
                <x-button-link :href="route('projects.export', request()->query())">
                    <x-icon name="arrow-down-tray" class="h-4 w-4" />
                    {{ __('Export to Excel') }}
                </x-button-link>
            @endif

            @can('create', \App\Models\Project::class)
                <x-button-link :href="route('projects.create')" variant="primary">
                    <x-icon name="plus" class="h-4 w-4" />
                    {{ __('New Project') }}
                </x-button-link>
            @endcan
        </x-page-heading>
    </x-slot>

    @php
        $assignableIds = $projects->filter(fn ($project) => auth()->user()->can('assign', $project))->toBase()
            ->map(fn ($project) => (string) $project->id)
            ->values();
        $deletableIds = $projects->filter(fn ($project) => auth()->user()->can('delete', $project))->toBase()
            ->map(fn ($project) => (string) $project->id)
            ->values();
        $selectableIds = $assignableIds->merge($deletableIds)->unique()->values();
        $hasFilters = (bool) array_filter(request()->only(['search', 'status', 'assignee_id', 'deca_no']));
        $statusTabs = ['' => __('All')] + collect(\App\Enums\ProjectStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->all();
        $checkboxClasses = 'h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800';
    @endphp

    <div x-data="{
            selected: @js(array_map('strval', (array) old('project_ids', []))),
            assignableIds: @js($assignableIds),
            deletableIds: @js($deletableIds),
            selectableIds: @js($selectableIds),
            get assignableSelected() {
                return this.selected.filter((id) => this.assignableIds.includes(id));
            },
            get deletableSelected() {
                return this.selected.filter((id) => this.deletableIds.includes(id));
            },
            get allSelected() {
                return this.selectableIds.length > 0 && this.selectableIds.every((id) => this.selected.includes(id));
            },
            get someSelected() {
                return this.selected.length > 0 && ! this.allSelected;
            },
            toggleAll() {
                this.selected = this.allSelected ? [] : [...this.selectableIds];
            },
        }">
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <x-alert>{{ session('status') }}</x-alert>
            @endif

            @foreach ([$errors->bulkAssign, $errors->bulkDelete] as $bulkErrors)
                @if ($bulkErrors->has('project_ids') || $bulkErrors->has('project_ids.*'))
                    <x-alert type="error">
                        @foreach ([...$bulkErrors->get('project_ids'), ...collect($bulkErrors->get('project_ids.*'))->flatten()->unique()] as $message)
                            <p>{{ $message }}</p>
                        @endforeach
                    </x-alert>
                @endif
            @endforeach

            {{-- Status tabs --}}
            <div class="overflow-x-auto">
                <nav class="inline-flex min-w-full gap-1 rounded-xl bg-white p-1 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10 sm:min-w-0" aria-label="{{ __('Status') }}">
                    @foreach ($statusTabs as $statusValue => $statusLabel)
                        @php $isCurrentTab = (string) request('status', '') === (string) $statusValue; @endphp
                        <a href="{{ route('projects.index', array_filter([...request()->except(['status', 'page']), 'status' => $statusValue])) }}"
                           @if ($isCurrentTab) aria-current="page" @endif
                           class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium transition {{ $isCurrentTab ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700/50 dark:hover:text-gray-100' }}">
                            {{ $statusLabel }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <x-card>
                {{-- Filters --}}
                <form method="GET" action="{{ route('projects.index') }}" class="grid grid-cols-1 gap-4 border-b border-gray-100 p-5 dark:border-gray-700 sm:grid-cols-2 sm:p-6 lg:grid-cols-12 lg:items-end">
                    @if (request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif

                    <div class="sm:col-span-2 lg:col-span-5">
                        <x-input-label for="search" :value="__('Search')" />
                        <div class="relative mt-1">
                            <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400">
                                <x-icon name="magnifying-glass" class="h-5 w-5" />
                            </div>
                            <x-text-input id="search" name="search" type="search" class="block w-full ps-10"
                                value="{{ request('search') }}" placeholder="{{ __('Project code, work code, Deca No., road...') }}" />
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <x-input-label for="deca_no" :value="__('Deca No.')" />
                        <x-text-input id="deca_no" name="deca_no" type="text" maxlength="50" pattern="[A-Za-z0-9\-]*" class="mt-1 block w-full"
                            title="{{ __('Letters, numbers and hyphens (-) only') }}" placeholder="{{ __('e.g. DC-1001') }}"
                            value="{{ request('deca_no') }}" />
                        <x-input-error :messages="$errors->get('deca_no')" class="mt-2" />
                    </div>

                    <div class="lg:col-span-3">
                        <x-input-label for="assignee_id" :value="__('Assignee')" />
                        <x-select-input id="assignee_id" name="assignee_id" class="mt-1 block w-full">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($assignees as $assignee)
                                <option value="{{ $assignee->id }}" @selected(request('assignee_id') == $assignee->id)>
                                    {{ $assignee->name }}
                                </option>
                            @endforeach
                        </x-select-input>
                    </div>

                    <div class="flex items-center gap-2 sm:col-span-2 lg:col-span-2">
                        <x-primary-button class="flex-1 lg:flex-none">
                            <x-icon name="funnel" class="h-4 w-4" />
                            {{ __('Filter') }}
                        </x-primary-button>

                        @if ($hasFilters)
                            <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-1 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700/50 dark:hover:text-gray-100">
                                <x-icon name="x-mark" class="h-4 w-4" />
                                {{ __('Reset') }}
                            </a>
                        @endif
                    </div>
                </form>

                {{-- Results summary / bulk actions --}}
                <div class="flex min-h-[3.75rem] flex-wrap items-center gap-3 px-5 py-3 sm:px-6"
                    x-bind:class="selected.length > 0 && 'bg-indigo-50 dark:bg-indigo-500/10'">
                    <p x-show="selected.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
                        @if ($projects->total() > 0)
                            {{ __('Showing :from–:to of :total projects', ['from' => $projects->firstItem(), 'to' => $projects->lastItem(), 'total' => $projects->total()]) }}
                        @else
                            {{ __('No projects found.') }}
                        @endif
                    </p>

                    <template x-if="selected.length > 0">
                        <span class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-800 dark:text-indigo-300">
                            <x-icon name="check-circle" class="h-5 w-5" />
                            <span x-text="@js(__(':count selected')).replace(':count', selected.length)"></span>
                        </span>
                    </template>

                    <div x-show="selected.length > 0" style="display: none;" class="flex flex-wrap items-center gap-2 sm:ms-auto">
                        <x-primary-button type="button" x-show="assignableSelected.length > 0" x-on:click="$dispatch('open-modal', 'bulk-assign-projects')">
                            <x-icon name="arrows-right-left" class="h-4 w-4" />
                            {{ __('Assign / Reassign Selected') }}
                        </x-primary-button>
                        @if ($deletableIds->isNotEmpty())
                            <x-danger-button type="button" x-show="deletableSelected.length > 0" x-on:click="$dispatch('open-modal', 'bulk-delete-projects')">
                                <x-icon name="trash" class="h-4 w-4" />
                                {{ __('Delete Selected') }}
                            </x-danger-button>
                        @endif
                        <button type="button" x-on:click="selected = []" class="rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-white hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                            {{ __('Clear selection') }}
                        </button>
                    </div>
                </div>

                {{-- Projects table --}}
                <div class="overflow-x-auto border-t border-gray-100 dark:border-gray-700">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th scope="col" class="w-12 ps-5 sm:ps-6 py-3">
                                    @if ($selectableIds->isNotEmpty())
                                        <input type="checkbox" x-bind:checked="allSelected" x-effect="$el.indeterminate = someSelected" x-on:change="toggleAll()"
                                            aria-label="{{ __('Select all projects on this page') }}"
                                            class="{{ $checkboxClasses }}">
                                    @endif
                                </th>
                                <th scope="col" class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Project Code') }}</th>
                                <th scope="col" class="hidden lg:table-cell px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Work Code') }}</th>
                                <th scope="col" class="hidden md:table-cell px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Deca No.') }}</th>
                                <th scope="col" class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Status') }}</th>
                                <th scope="col" class="hidden sm:table-cell px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Assignee') }}</th>
                                <th scope="col" class="hidden xl:table-cell px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Assigned On') }}</th>
                                <th scope="col" class="hidden lg:table-cell px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Received Date') }}</th>
                                <th scope="col" class="pe-5 sm:pe-6 py-3"><span class="sr-only">{{ __('Actions') }}</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @forelse ($projects as $project)
                                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/30" x-bind:class="selected.includes(@js((string) $project->id)) && '!bg-indigo-50/60 dark:!bg-indigo-500/10'">
                                    <td class="w-12 ps-5 sm:ps-6 py-4">
                                        @if ($selectableIds->contains((string) $project->id))
                                            <input type="checkbox" value="{{ $project->id }}" x-model="selected"
                                                aria-label="{{ __('Select :code', ['code' => $project->project_code]) }}"
                                                class="{{ $checkboxClasses }}">
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <a href="{{ route('projects.show', $project) }}" class="font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400">{{ $project->project_code }}</a>
                                        <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 lg:hidden">
                                            {{ $project->work_code }}@if ($project->deca_no)<span class="md:hidden"> · {{ $project->deca_no }}</span>@endif
                                        </div>
                                    </td>
                                    <td class="hidden lg:table-cell px-4 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">{{ $project->work_code }}</td>
                                    <td class="hidden md:table-cell px-4 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                        @if ($project->deca_no)
                                            <span class="rounded-md bg-gray-100 px-2 py-0.5 font-mono text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-300">{{ $project->deca_no }}</span>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <x-status-badge :status="$project->status" />
                                    </td>
                                    <td class="hidden sm:table-cell px-4 py-4 whitespace-nowrap">
                                        @if ($project->assignee)
                                            <div class="flex items-center gap-2">
                                                <x-avatar :name="$project->assignee->name" size="sm" />
                                                <span class="text-gray-900 dark:text-gray-100">{{ $project->assignee->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600">—</span>
                                        @endif
                                    </td>
                                    <td class="hidden xl:table-cell px-4 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">{{ $project->assignee_id ? $project->latestAssignmentLog?->assignedDate()?->format('d-M-y') ?? '—' : '—' }}</td>
                                    <td class="hidden lg:table-cell px-4 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">{{ $project->received_date->format('d-M-y') }}</td>
                                    <td class="pe-5 sm:pe-6 py-4 whitespace-nowrap text-end">
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
                                            <button type="button" x-ref="trigger" x-on:click="toggle()" x-bind:aria-expanded="open"
                                                title="{{ __('Actions') }}"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                                                <span class="sr-only">{{ __('Actions') }}</span>
                                                <x-icon name="ellipsis" class="h-5 w-5" />
                                            </button>

                                            <div x-show="open" x-transition.opacity.duration.100ms x-bind:style="menuStyle" style="display: none;"
                                                class="fixed z-50 w-44 overflow-hidden rounded-xl bg-white py-1 text-start shadow-lg ring-1 ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10">
                                                <x-dropdown-link :href="route('projects.show', $project)">
                                                    <x-icon name="eye" class="h-4 w-4 text-gray-400" />
                                                    {{ __('View') }}
                                                </x-dropdown-link>
                                                @can('update', $project)
                                                    <x-dropdown-link :href="route('projects.edit', $project)">
                                                        <x-icon name="pencil-square" class="h-4 w-4 text-gray-400" />
                                                        {{ __('Edit') }}
                                                    </x-dropdown-link>
                                                @endcan
                                                @can('delete', $project)
                                                    <div class="my-1 border-t border-gray-100 dark:border-gray-700"></div>
                                                    <button type="button"
                                                        x-on:click="open = false; $dispatch('confirm-project-deletion', { url: @js(route('projects.destroy', $project)), code: @js($project->project_code) })"
                                                        class="flex w-full items-center gap-2 px-4 py-2 text-start text-sm leading-5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 focus:outline-none focus:bg-red-50 dark:focus:bg-red-500/10 transition duration-150 ease-in-out">
                                                        <x-icon name="trash" class="h-4 w-4" />
                                                        {{ __('Delete') }}
                                                    </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-16 text-center">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500">
                                            <x-icon name="folder" class="h-6 w-6" />
                                        </div>
                                        <p class="mt-3 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('No projects found.') }}</p>
                                        @if ($hasFilters)
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Try a different search or clear the filters.') }}</p>
                                            <a href="{{ route('projects.index') }}" class="mt-4 inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">{{ __('Reset') }}</a>
                                        @else
                                            @can('create', \App\Models\Project::class)
                                                <div class="mt-4">
                                                    <x-button-link :href="route('projects.create')" variant="primary">
                                                        <x-icon name="plus" class="h-4 w-4" />
                                                        {{ __('New Project') }}
                                                    </x-button-link>
                                                </div>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($projects->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-700 sm:px-6">
                        {{ $projects->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <div x-data="{ deleteUrl: '', projectCode: '' }"
        x-on:confirm-project-deletion.window="deleteUrl = $event.detail.url; projectCode = $event.detail.code; $dispatch('open-modal', 'confirm-project-deletion')">
        <x-modal name="confirm-project-deletion" maxWidth="lg" focusable>
            <form method="POST" x-bind:action="deleteUrl">
                @csrf
                @method('DELETE')

                <div class="flex gap-4 p-6">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                        <x-icon name="exclamation-triangle" class="h-6 w-6" />
                    </span>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Are you sure you want to delete this project?') }}
                            <span class="font-semibold" x-text="projectCode"></span>
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('This action cannot be undone. All assignment history for this project will also be deleted.') }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 bg-gray-50 px-6 py-4 dark:bg-gray-900/40 sm:flex-row sm:justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>
                    <x-danger-button>
                        {{ __('Delete') }}
                    </x-danger-button>
                </div>
            </form>
        </x-modal>
    </div>

    <x-modal name="bulk-assign-projects" :show="$errors->bulkAssign->hasAny(['assignee_id', 'assigned_on', 'deca_no', 'note'])" focusable>
        <form id="bulk-assign-form" method="POST" action="{{ route('projects.bulk-assign') }}">
            @csrf

            <template x-for="id in assignableSelected" :key="id">
                <input type="hidden" name="project_ids[]" x-bind:value="id">
            </template>

            <div class="flex items-start gap-4 border-b border-gray-100 px-6 py-5 dark:border-gray-700">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="arrows-right-left" class="h-6 w-6" />
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Assign / Reassign Selected') }}
                        </h2>
                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300"
                            x-text="@js(__(':count selected')).replace(':count', assignableSelected.length)"></span>
                    </div>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                        x-text="@js(__('The :count selected projects will be assigned to the chosen user. Pending projects are assigned; in-progress projects are reassigned from their current assignee.')).replace(':count', assignableSelected.length)"></p>
                </div>
                <button type="button" x-on:click="$dispatch('close')" aria-label="{{ __('Close') }}"
                    class="-me-2 -mt-1 rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="grid grid-cols-1 gap-5 px-6 py-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input-label for="bulk_assignee_id" :value="__('Assign To')" required />
                    <x-select-input id="bulk_assignee_id" name="assignee_id" required class="mt-1 block w-full">
                        <option value="">{{ __('Select a user') }}</option>
                        @foreach ($assignees as $assignee)
                            <option value="{{ $assignee->id }}" @selected(old('assignee_id') == $assignee->id && $errors->bulkAssign->isNotEmpty())>
                                {{ $assignee->name }}
                            </option>
                        @endforeach
                    </x-select-input>
                    <x-input-error :messages="$errors->bulkAssign->get('assignee_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="bulk_assigned_on" :value="__('Assigned On')" required />
                    <x-date-input id="bulk_assigned_on" name="assigned_on" required width="w-full"
                        :value="old('assigned_on', now()->format('Y-m-d'))"
                        :max="now()->format('Y-m-d')" />
                    <x-input-error :messages="$errors->bulkAssign->get('assigned_on')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="bulk_deca_no" :value="__('Deca No. (optional)')" />
                    <x-text-input id="bulk_deca_no" name="deca_no" type="text" maxlength="50" pattern="[A-Za-z0-9\-]*" class="mt-1 block w-full font-mono"
                        title="{{ __('Letters, numbers and hyphens (-) only') }}" placeholder="{{ __('e.g. DC-1001') }}"
                        :value="$errors->bulkAssign->isNotEmpty() ? old('deca_no') : ''" />
                    <x-input-error :messages="$errors->bulkAssign->get('deca_no')" class="mt-2" />
                </div>

                <div class="sm:col-span-2">
                    <x-input-label for="bulk_note" :value="__('Note (optional)')" />
                    <x-textarea-input id="bulk_note" name="note" rows="3" class="mt-1 block w-full">{{ old('note') }}</x-textarea-input>
                    <x-input-error :messages="$errors->bulkAssign->get('note')" class="mt-2" />
                </div>
            </div>

            <div class="flex flex-col-reverse gap-2 bg-gray-50 px-6 py-4 dark:bg-gray-900/40 sm:flex-row sm:justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button x-bind:disabled="assignableSelected.length === 0">
                    <x-icon name="check" class="h-4 w-4" />
                    {{ __('Assign') }}
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    @if ($deletableIds->isNotEmpty())
    <x-modal name="bulk-delete-projects" maxWidth="lg" focusable>
        <form method="POST" action="{{ route('projects.bulk-destroy') }}">
            @csrf
            @method('DELETE')

            <template x-for="id in deletableSelected" :key="id">
                <input type="hidden" name="project_ids[]" x-bind:value="id">
            </template>

            <div class="flex gap-4 p-6">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                    <x-icon name="exclamation-triangle" class="h-6 w-6" />
                </span>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                        x-text="@js(__('Are you sure you want to delete the :count selected projects?')).replace(':count', deletableSelected.length)"></h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('This action cannot be undone. All assignment history for these projects will also be deleted.') }}
                    </p>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-2 bg-gray-50 px-6 py-4 dark:bg-gray-900/40 sm:flex-row sm:justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-danger-button x-bind:disabled="deletableSelected.length === 0">
                    {{ __('Delete') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
    @endif
    </div>
</x-app-layout>
