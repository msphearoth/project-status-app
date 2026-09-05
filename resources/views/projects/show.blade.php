<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Project') }}: {{ $project->project_code }}
                <span class="ms-2 px-2 py-1 text-xs font-semibold rounded-full {{ $project->status->badgeClass() }}">
                    {{ $project->status->label() }}
                </span>
            </h2>

            <div class="flex items-center gap-3">
                @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}"
                       class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                        {{ __('Edit') }}
                    </a>
                @endcan
                @can('delete', $project)
                    <x-danger-button
                        x-data=""
                        x-on:click.prevent="$dispatch('open-modal', 'confirm-project-deletion')"
                    >{{ __('Delete') }}</x-danger-button>

                    <x-modal name="confirm-project-deletion" focusable>
                        <form method="POST" action="{{ route('projects.destroy', $project) }}" class="p-6">
                            @csrf
                            @method('DELETE')

                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Are you sure you want to delete this project?') }}
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
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 bg-green-100 dark:bg-green-800/30 text-green-800 dark:text-green-400 rounded-md">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Project Details') }}</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Year') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $project->year }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Work Code') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $project->work_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('On Road') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $project->on_road }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Start Road') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $project->start_road }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('End Road') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $project->end_road }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Pipe Type') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $project->pipe_type }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Pipe Diameter') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $project->pipe_diameter }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Pipe Length') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $project->pipe_length }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Received Date') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $project->received_date->format('Y-m-d') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Created By') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $project->creator->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Current Assignee') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $project->assignee?->name ?? __('Unassigned') }}</dd>
                    </div>
                    @if ($project->status === \App\Enums\ProjectStatus::Completed)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Project Amount') }}</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ number_format((float) $project->project_amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Request Number') }}</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $project->request_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Completed At') }}</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $project->completed_at->format('Y-m-d H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            @can('assign', $project)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        {{ $project->status === \App\Enums\ProjectStatus::Pending ? __('Assign Project') : __('Reassign Project') }}
                    </h3>
                    <form method="POST" action="{{ route('projects.assign', $project) }}" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="assignee_id" :value="__('Assign To')" />
                            <select id="assignee_id" name="assignee_id" required
                                class="mt-1 block w-full sm:w-72 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">{{ __('Select a user') }}</option>
                                @foreach ($users as $user)
                                    @if ($user->id !== $project->assignee_id)
                                        <option value="{{ $user->id }}" @selected(old('assignee_id') == $user->id)>
                                            {{ $user->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('assignee_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="note" :value="__('Note (optional)')" />
                            <textarea id="note" name="note" rows="2"
                                class="mt-1 block w-full sm:w-96 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('note') }}</textarea>
                            <x-input-error :messages="$errors->get('note')" class="mt-2" />
                        </div>

                        <x-primary-button>
                            {{ $project->status === \App\Enums\ProjectStatus::Pending ? __('Assign') : __('Reassign') }}
                        </x-primary-button>
                    </form>
                </div>
            @endcan

            @can('complete', $project)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('Complete Project') }}</h3>
                    <form method="POST" action="{{ route('projects.complete', $project) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="project_amount" :value="__('Project Amount')" />
                                <x-text-input id="project_amount" name="project_amount" type="number" step="0.01" min="0"
                                    class="mt-1 block w-full" :value="old('project_amount')" required />
                                <x-input-error :messages="$errors->get('project_amount')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="request_number" :value="__('Request Number')" />
                                <x-text-input id="request_number" name="request_number" type="text"
                                    class="mt-1 block w-full" :value="old('request_number')" required />
                                <x-input-error :messages="$errors->get('request_number')" class="mt-2" />
                            </div>
                        </div>

                        <x-primary-button>
                            {{ __('Complete Project') }}
                        </x-primary-button>
                    </form>
                </div>
            @endcan

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 p-6 pb-0">{{ __('Assignment History') }}</h3>
                <div class="overflow-x-auto p-6">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Action') }}</th>
                                <th class="px-4 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('By') }}</th>
                                <th class="px-4 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('To') }}</th>
                                <th class="px-4 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Note') }}</th>
                                <th class="px-4 py-2 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($project->assignmentLogs as $log)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $log->action->label() }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $log->assignedBy->name }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $log->assignedTo?->name ?? '—' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $log->note ?? '—' }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('No assignment history yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
