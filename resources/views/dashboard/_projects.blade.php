<div class="-mx-6 overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700">
                <th scope="col" class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Project Code') }}</th>
                <th scope="col" class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Work Code') }}</th>
                <th scope="col" class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Deca No.') }}</th>
                <th scope="col" class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Assignee') }}</th>
                <th scope="col" class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Assigned On') }}</th>
                <th scope="col" class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Days') }}</th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Received Date') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
            @forelse ($projects as $project)
                @php
                    $days = $daysSinceAssignment($project);
                    $daysClasses = match (true) {
                        $days < 5 => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/30',
                        $days < 10 => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/30',
                        default => 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/30',
                    };
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-6 py-3 whitespace-nowrap font-medium">
                        <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 hover:underline">{{ $project->project_code }}</a>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-400">{{ $project->work_code }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-400">{{ $project->deca_no ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-gray-900 dark:text-gray-100">{{ $project->assignee?->name ?? __('Unassigned') }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-400">{{ $project->latestAssignmentLog?->assignedDate()?->format('d-M-y') ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-end">
                        <span class="inline-flex min-w-[2.25rem] justify-center rounded-full px-2 py-0.5 text-xs font-semibold tabular-nums ring-1 ring-inset {{ $daysClasses }}">{{ $days }}</span>
                    </td>
                    <td class="px-6 py-3 whitespace-nowrap text-gray-600 dark:text-gray-400">{{ $project->received_date->format('d-M-y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                        {{ __('No projects in this range any more.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
