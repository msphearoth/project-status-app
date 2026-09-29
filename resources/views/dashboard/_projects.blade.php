<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Project Code') }}</th>
                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Work Code') }}</th>
                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Deca No.') }}</th>
                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Assignee') }}</th>
                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Assigned On') }}</th>
                <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Days') }}</th>
                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Received Date') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($projects as $project)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $project->project_code }}</a>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $project->work_code }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $project->deca_no ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $project->assignee?->name ?? __('Unassigned') }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $project->latestAssignmentLog?->assignedDate()?->format('d-M-y') ?? '—' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-end text-sm text-gray-900 dark:text-gray-100">{{ $daysSinceAssignment($project) }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $project->received_date->format('d-M-y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        {{ __('No projects in this range any more.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
