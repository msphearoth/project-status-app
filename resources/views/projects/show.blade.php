<x-app-layout>
    <x-slot name="header">
        <x-page-heading :title="__('Project').': '.$project->project_code" :back="route('projects.index')"
            :description="__('Received :date · Created by :name', ['date' => $project->received_date->format('d-M-y'), 'name' => $project->creator->name])">
            <x-slot name="badge">
                <x-status-badge :status="$project->status" />
            </x-slot>

            @can('update', $project)
                <x-button-link :href="route('projects.edit', $project)">
                    <x-icon name="pencil-square" class="h-4 w-4" />
                    {{ __('Edit') }}
                </x-button-link>
            @endcan
            @can('reopen', $project)
                <button type="button"
                    x-data=""
                    x-on:click="$dispatch('confirm-project-reopen', { url: @js(route('projects.reopen', $project)), code: @js($project->project_code) })"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-amber-700 shadow-sm ring-1 ring-inset ring-amber-300 hover:bg-amber-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:bg-gray-800 dark:text-amber-400 dark:ring-amber-500/40 dark:hover:bg-amber-500/10 dark:focus-visible:ring-offset-gray-800 transition ease-in-out duration-150">
                    <x-icon name="arrow-uturn-left" class="h-4 w-4" />
                    {{ __('Revert to In Progress') }}
                </button>
            @endcan
            @can('delete', $project)
                <x-danger-button
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-project-deletion')"
                >
                    <x-icon name="trash" class="h-4 w-4" />
                    {{ __('Delete') }}
                </x-danger-button>

                <x-modal name="confirm-project-deletion" maxWidth="lg" focusable>
                    <form method="POST" action="{{ route('projects.destroy', $project) }}">
                        @csrf
                        @method('DELETE')

                        <div class="flex gap-4 p-6">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                                <x-icon name="exclamation-triangle" class="h-6 w-6" />
                            </span>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('Are you sure you want to delete this project?') }}
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
            @endcan
        </x-page-heading>
    </x-slot>

    @php
        $isPending = $project->status === \App\Enums\ProjectStatus::Pending;
        $details = [
            ['label' => __('Year'), 'value' => $project->year],
            ['label' => __('Work Code'), 'value' => $project->work_code],
            ['label' => __('Deca No.'), 'value' => $project->deca_no, 'mono' => true],
            ['label' => __('On Road'), 'value' => $project->on_road],
            ['label' => __('Start Road'), 'value' => $project->start_road],
            ['label' => __('End Road'), 'value' => $project->end_road],
            ['label' => __('Pipe Type'), 'value' => $project->pipe_type],
            ['label' => __('Pipe Diameter'), 'value' => $project->pipe_diameter],
            ['label' => __('Pipe Length'), 'value' => $project->pipe_length],
            ['label' => __('Received Date'), 'value' => $project->received_date->format('d-M-y')],
            ['label' => __('Created By'), 'value' => $project->creator->name],
        ];
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <x-alert>{{ session('status') }}</x-alert>
            @endif

            <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">

                {{-- Project details --}}
                <x-card :title="__('Project Details')" icon="document-text" class="lg:col-span-2">
                    <div class="flex flex-wrap items-center gap-4 border-b border-gray-100 px-5 py-4 dark:border-gray-700 sm:px-6">
                        <div class="flex items-center gap-3">
                            @if ($project->assignee)
                                <x-avatar :name="$project->assignee->name" size="lg" />
                            @else
                                <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-dashed border-gray-300 text-gray-400 dark:border-gray-600">
                                    <x-icon name="user" class="h-5 w-5" />
                                </span>
                            @endif
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Current Assignee') }}</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $project->assignee?->name ?? __('Unassigned') }}</p>
                            </div>
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 gap-x-6 gap-y-5 p-5 sm:grid-cols-2 sm:p-6 xl:grid-cols-3">
                        @foreach ($details as $detail)
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $detail['label'] }}</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100 break-words">
                                    @if (filled($detail['value']))
                                        @if ($detail['mono'] ?? false)
                                            <span class="rounded-md bg-gray-100 px-2 py-0.5 font-mono text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-300">{{ $detail['value'] }}</span>
                                        @else
                                            {{ $detail['value'] }}
                                        @endif
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>

                    @if ($project->status === \App\Enums\ProjectStatus::Completed)
                        <dl class="grid grid-cols-1 gap-x-6 gap-y-5 rounded-b-xl border-t border-emerald-100 bg-emerald-50/60 p-5 dark:border-emerald-500/20 dark:bg-emerald-500/5 sm:grid-cols-3 sm:p-6">
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-emerald-700 dark:text-emerald-400">{{ __('Project Amount') }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100 tabular-nums">{{ $project->project_amount !== null ? number_format((float) $project->project_amount, 2) : '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-emerald-700 dark:text-emerald-400">{{ __('Request Number') }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $project->request_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-emerald-700 dark:text-emerald-400">{{ __('Completed At') }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $project->completed_at->format('Y-m-d H:i') }}</dd>
                            </div>
                        </dl>
                    @endif
                </x-card>

                {{-- Actions sidebar --}}
                <div class="space-y-6 lg:col-start-3 lg:row-span-2 lg:row-start-1 lg:sticky lg:top-24">
                    @can('assign', $project)
                        <x-card :title="$isPending ? __('Assign Project') : __('Reassign Project')" :icon="$isPending ? 'user-plus' : 'arrows-right-left'"
                            :description="$isPending ? __('Hand this project to a team member.') : __('Move this project to another team member.')">
                            <form method="POST" action="{{ route('projects.assign', $project) }}">
                                @csrf

                                <div class="space-y-5 p-5 sm:p-6">
                                    @if (! $isPending)
                                        <div>
                                            <x-input-label for="assigned_from" :value="__('Assign Project From')" required />
                                            <x-select-input id="assigned_from" name="assigned_from" required class="mt-1 block w-full">
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}" @selected(old('assigned_from', $project->assignee_id) == $user->id)>
                                                        {{ $user->name }}
                                                    </option>
                                                @endforeach
                                            </x-select-input>
                                            <x-input-error :messages="$errors->get('assigned_from')" class="mt-2" />
                                        </div>

                                        <div class="flex justify-center text-gray-300 dark:text-gray-600" aria-hidden="true">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                                            </svg>
                                        </div>
                                    @endif

                                    <div>
                                        <x-input-label for="assignee_id" :value="__('Assign To')" required />
                                        <x-select-input id="assignee_id" name="assignee_id" required class="mt-1 block w-full">
                                            <option value="">{{ __('Select a user') }}</option>
                                            @foreach ($users as $user)
                                                @if ($user->id !== $project->assignee_id)
                                                    <option value="{{ $user->id }}" @selected(old('assignee_id') == $user->id)>
                                                        {{ $user->name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </x-select-input>
                                        <x-input-error :messages="$errors->get('assignee_id')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="assigned_on" :value="__('Assigned On')" required />
                                        <x-date-input id="assigned_on" name="assigned_on" required width="w-full"
                                            :value="old('assigned_on', now()->format('Y-m-d'))"
                                            :min="$project->received_date->format('Y-m-d')"
                                            :max="now()->format('Y-m-d')" />
                                        <x-input-error :messages="$errors->get('assigned_on')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="assign_deca_no" :value="__('Deca No. (optional)')" />
                                        <x-text-input id="assign_deca_no" name="deca_no" type="text" maxlength="50" pattern="[A-Za-z0-9\-]*" class="mt-1 block w-full font-mono"
                                            title="{{ __('Letters, numbers and hyphens (-) only') }}" placeholder="{{ __('e.g. DC-1001') }}"
                                            :value="old('deca_no')" />
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Leave blank to keep the current Deca No.') }}</p>
                                        <x-input-error :messages="$errors->get('deca_no')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="note" :value="__('Note (optional)')" />
                                        <x-textarea-input id="note" name="note" rows="3" class="mt-1 block w-full">{{ old('note') }}</x-textarea-input>
                                        <x-input-error :messages="$errors->get('note')" class="mt-2" />
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2 rounded-b-xl border-t border-gray-100 bg-gray-50 px-5 py-4 dark:border-gray-700 dark:bg-gray-900/40 sm:px-6">
                                    <x-primary-button class="w-full">
                                        <x-icon name="check" class="h-4 w-4" />
                                        {{ $isPending ? __('Assign') : __('Reassign') }}
                                    </x-primary-button>

                                    @can('unassign', $project)
                                        <button type="button"
                                            x-data=""
                                            x-on:click.prevent="$dispatch('open-modal', 'confirm-project-unassign')"
                                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                                            <x-icon name="user-minus" class="h-4 w-4" />
                                            {{ __('Remove Assignee') }}
                                        </button>
                                    @endcan
                                </div>
                            </form>

                            @can('unassign', $project)
                                <x-modal name="confirm-project-unassign" maxWidth="lg" focusable>
                                    <form method="POST" action="{{ route('projects.unassign', $project) }}">
                                        @csrf
                                        @method('DELETE')

                                        <div class="flex gap-4 p-6">
                                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                                                <x-icon name="user-minus" class="h-6 w-6" />
                                            </span>
                                            <div>
                                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ __('Remove :name from this project?', ['name' => $project->assignee?->name]) }}
                                                </h2>
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                    {{ __('The project will return to Pending with no assignee. The assignment history is kept.') }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex flex-col-reverse gap-2 bg-gray-50 px-6 py-4 dark:bg-gray-900/40 sm:flex-row sm:justify-end">
                                            <x-secondary-button x-on:click="$dispatch('close')">
                                                {{ __('Cancel') }}
                                            </x-secondary-button>
                                            <x-danger-button>
                                                {{ __('Remove Assignee') }}
                                            </x-danger-button>
                                        </div>
                                    </form>
                                </x-modal>
                            @endcan
                        </x-card>
                    @endcan

                    @if ($project->status === \App\Enums\ProjectStatus::InProgress && auth()->user()->cannot('assign', $project))
                        <x-alert type="info">
                            {{ __('This project is assigned to :name. Only the assignee or an admin can update, reassign or complete it.', ['name' => $project->assignee?->name]) }}
                        </x-alert>
                    @endif

                    @can('complete', $project)
                        <x-card :title="__('Complete Project')" icon="check-circle" :description="__('Record the request number to close this project.')">
                            <form method="POST" action="{{ route('projects.complete', $project) }}">
                                @csrf
                                <div class="space-y-5 p-5 sm:p-6">
                                    <div>
                                        <x-input-label for="request_number" :value="__('Request Number')" required />
                                        <x-text-input id="request_number" name="request_number" type="text"
                                            class="mt-1 block w-full" :value="old('request_number', $project->request_number)" required />
                                        <x-input-error :messages="$errors->get('request_number')" class="mt-2" />
                                    </div>
                                </div>

                                <div class="rounded-b-xl border-t border-gray-100 bg-gray-50 px-5 py-4 dark:border-gray-700 dark:bg-gray-900/40 sm:px-6">
                                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800">
                                        <x-icon name="check-circle" class="h-4 w-4" />
                                        {{ __('Complete Project') }}
                                    </button>
                                </div>
                            </form>
                        </x-card>
                    @endcan
                </div>

                {{-- Assignment history timeline --}}
                <x-card :title="__('Assignment History')" icon="clock" class="lg:col-span-2">
                    <div class="p-5 sm:p-6">
                        @if ($project->assignmentLogs->isEmpty())
                            <div class="py-8 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500">
                                    <x-icon name="clock" class="h-6 w-6" />
                                </div>
                                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ __('No assignment history yet.') }}</p>
                            </div>
                        @else
                            <ol class="space-y-0">
                                @foreach ($project->assignmentLogs as $log)
                                    <li class="relative flex gap-4 {{ $loop->last ? '' : 'pb-8' }}">
                                        @unless ($loop->last)
                                            <span class="absolute start-[1.125rem] top-10 bottom-0 w-px bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                        @endunless
                                        <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $log->action->badgeClass() }}">
                                            <x-icon :name="$log->action->icon()" class="h-4 w-4" />
                                        </span>

                                        <div class="min-w-0 flex-1 pt-1">
                                            <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                                                <p class="text-sm text-gray-900 dark:text-gray-100">
                                                    <span class="font-semibold">{{ $log->action->label() }}</span>
                                                    <span class="text-gray-500 dark:text-gray-400">{{ __('By') }}</span>
                                                    <span class="font-medium">{{ $log->assignedBy->name }}</span>
                                                </p>
                                                <time datetime="{{ $log->created_at->toIso8601String() }}" title="{{ $log->created_at->format('Y-m-d H:i') }}"
                                                    class="text-xs text-gray-500 dark:text-gray-400">{{ $log->created_at->format('Y-m-d H:i') }}</time>
                                            </div>

                                            @if ($log->assignedFrom || $log->assignedTo)
                                                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                                                    @if ($log->assignedFrom)
                                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 py-0.5 ps-0.5 pe-2.5 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                            <x-avatar :name="$log->assignedFrom->name" size="sm" />
                                                            <span><span class="sr-only">{{ __('From') }}: </span>{{ $log->assignedFrom->name }}</span>
                                                        </span>
                                                    @endif
                                                    @if ($log->assignedFrom && $log->assignedTo)
                                                        <x-icon name="arrow-right" class="h-4 w-4 text-gray-400 rtl:rotate-180" />
                                                    @endif
                                                    @if ($log->assignedTo)
                                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 py-0.5 ps-0.5 pe-2.5 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                            <x-avatar :name="$log->assignedTo->name" size="sm" />
                                                            <span><span class="sr-only">{{ __('To') }}: </span>{{ $log->assignedTo->name }}</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif

                                            @if ($log->assigned_on || $log->deca_no)
                                                <dl class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-xs">
                                                    @if ($log->assigned_on)
                                                        <div class="flex items-center gap-1.5">
                                                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Assigned On') }}:</dt>
                                                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $log->assigned_on->format('d-M-y') }}</dd>
                                                        </div>
                                                    @endif
                                                    @if ($log->deca_no)
                                                        <div class="flex items-center gap-1.5">
                                                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Deca No.') }}:</dt>
                                                            <dd class="rounded bg-gray-100 px-1.5 py-0.5 font-mono font-medium text-gray-900 dark:bg-gray-700 dark:text-gray-100">{{ $log->deca_no }}</dd>
                                                        </div>
                                                    @endif
                                                </dl>
                                            @endif

                                            @if ($log->note)
                                                <p class="mt-3 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-700 ring-1 ring-inset ring-gray-200 dark:bg-gray-900/40 dark:text-gray-300 dark:ring-gray-700 whitespace-pre-line">{{ $log->note }}</p>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </div>
                </x-card>
            </div>
        </div>
    </div>

    @can('reopen', $project)
        @include('projects._reopen-modal')
    @endcan
</x-app-layout>
