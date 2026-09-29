<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>

            <a href="{{ route('projects.index') }}"
               class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                {{ __('View All Projects') }}
            </a>
        </div>
    </x-slot>

    @php
        $buckets = [
            'under5' => __('Less than 5 Days'),
            'between5and10' => __('5 to 10 Days'),
            'over10' => __('10 Days or More'),
        ];
    @endphp

    <div class="py-12"
        x-data="{
            title: '',
            html: '',
            loading: false,
            failed: false,
            async drillDown(params, title) {
                this.title = title;
                this.html = '';
                this.failed = false;
                this.loading = true;
                this.$dispatch('open-modal', 'dashboard-projects');

                try {
                    const response = await fetch(@js(route('dashboard.projects')) + '?' + new URLSearchParams(params), {
                        headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (! response.ok) {
                        throw new Error(response.statusText);
                    }
                    this.html = await response.text();
                } catch (error) {
                    this.failed = true;
                } finally {
                    this.loading = false;
                }
            },
        }">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6 pb-0 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('In-Progress Projects by Assignee') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Currently assigned projects awaiting completion, grouped by year and their current assignee, and how many days it has been since assignment.') }}</p>
                    </div>

                    @if ($rows->isNotEmpty())
                        <a href="{{ route('dashboard.export') }}"
                           class="inline-flex items-center shrink-0 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                            {{ __('Export to Excel') }}
                        </a>
                    @endif
                </div>

                <div class="overflow-x-auto p-6">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Year') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Assignee') }}</th>
                                <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Less than 5 Days') }}</th>
                                <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('5 to 10 Days') }}</th>
                                <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('10 Days or More') }}</th>
                                <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($rows as $row)
                                <tr>
                                    @if ($row['yearRowspan'])
                                        <td rowspan="{{ $row['yearRowspan'] }}"
                                            class="px-4 py-3 align-top whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100 border-e border-gray-200 dark:border-gray-700">
                                            {{ $row['year'] }}
                                        </td>
                                    @endif
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $row['assignee']?->name ?? __('Unassigned') }}</td>
                                    @foreach ($buckets as $bucket => $bucketLabel)
                                        <td class="px-4 py-3 whitespace-nowrap text-end text-sm text-gray-500 dark:text-gray-400">
                                            @if ($row[$bucket] > 0)
                                                <button type="button"
                                                    x-on:click="drillDown(@js(array_filter(['bucket' => $bucket, 'year' => $row['year'], 'assignee_id' => $row['assignee']?->id])), @js($row['year'].' · '.($row['assignee']?->name ?? __('Unassigned')).' · '.$bucketLabel))"
                                                    class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded">
                                                    {{ $row[$bucket] }}
                                                </button>
                                            @else
                                                0
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 whitespace-nowrap text-end text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $row['total'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('No in-progress projects.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($rows->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 dark:border-gray-700 font-semibold">
                                    <td colspan="2" class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ __('Total') }}</td>
                                    @foreach ($buckets as $bucket => $bucketLabel)
                                        <td class="px-4 py-3 whitespace-nowrap text-end text-sm text-gray-900 dark:text-gray-100">
                                            @if ($rows->sum($bucket) > 0)
                                                <button type="button"
                                                    x-on:click="drillDown(@js(['bucket' => $bucket]), @js(__('All assignees').' · '.$bucketLabel))"
                                                    class="text-indigo-600 dark:text-indigo-400 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded">
                                                    {{ $rows->sum($bucket) }}
                                                </button>
                                            @else
                                                0
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 whitespace-nowrap text-end text-sm text-gray-900 dark:text-gray-100">{{ $rows->sum('total') }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <x-modal name="dashboard-projects" maxWidth="4xl">
            <div class="p-6">
                <div class="flex items-start justify-between gap-4">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100" x-text="title"></h2>
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Close') }}
                    </x-secondary-button>
                </div>

                <div class="mt-4">
                    <p x-show="loading" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Loading...') }}</p>
                    <p x-show="failed" style="display: none;" class="py-8 text-center text-sm text-red-600 dark:text-red-400">{{ __('Could not load the projects. Please try again.') }}</p>
                    <div x-show="! loading && ! failed" x-html="html"></div>
                </div>
            </div>
        </x-modal>
    </div>
</x-app-layout>
