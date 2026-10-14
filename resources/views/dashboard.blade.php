<x-app-layout>
    <x-slot name="header">
        <x-page-heading :title="__('Dashboard')" :description="__('Welcome back, :name', ['name' => Auth::user()->name])">
            <x-button-link :href="route('projects.index')">
                <x-icon name="folder" class="h-4 w-4" />
                {{ __('View All Projects') }}
            </x-button-link>
            @can('create', \App\Models\Project::class)
                <x-button-link :href="route('projects.create')" variant="primary">
                    <x-icon name="plus" class="h-4 w-4" />
                    {{ __('New Project') }}
                </x-button-link>
            @endcan
        </x-page-heading>
    </x-slot>

    @php
        $buckets = [
            'under5' => ['label' => __('Less than 5 Days'), 'bar' => 'bg-emerald-500', 'pill' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/30 dark:hover:bg-emerald-500/20'],
            'between5and10' => ['label' => __('5 to 10 Days'), 'bar' => 'bg-amber-400', 'pill' => 'bg-amber-50 text-amber-700 ring-amber-600/20 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/30 dark:hover:bg-amber-500/20'],
            'over10' => ['label' => __('10 Days or More'), 'bar' => 'bg-red-500', 'pill' => 'bg-red-50 text-red-700 ring-red-600/20 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/30 dark:hover:bg-red-500/20'],
        ];
        $inProgressTotal = $rows->sum('total');
        $overdueTotal = $rows->sum('over10');
        $statCards = [
            ['status' => \App\Enums\ProjectStatus::Pending, 'icon' => 'inbox', 'hint' => __('Awaiting assignment'), 'tile' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400'],
            ['status' => \App\Enums\ProjectStatus::InProgress, 'icon' => 'clock', 'hint' => __('Currently being worked on'), 'tile' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400'],
            ['status' => \App\Enums\ProjectStatus::Completed, 'icon' => 'check-circle', 'hint' => __('Finished projects'), 'tile' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'],
        ];
    @endphp

    <div class="py-8"
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Summary cards --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($statCards as $card)
                    <a href="{{ route('projects.index', ['status' => $card['status']->value]) }}"
                       class="group flex items-center gap-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-900/5 transition hover:shadow-md hover:ring-indigo-500/30 dark:bg-gray-800 dark:ring-white/10 dark:hover:ring-indigo-400/40">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $card['tile'] }}">
                            <x-icon :name="$card['icon']" class="h-6 w-6" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $card['status']->label() }}</p>
                            <p class="text-2xl font-bold tabular-nums text-gray-900 dark:text-gray-100">{{ number_format($statusCounts[$card['status']->value]) }}</p>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $card['hint'] }}</p>
                        </div>
                        <x-icon name="arrow-right" class="ms-auto h-4 w-4 shrink-0 text-gray-300 transition group-hover:translate-x-0.5 group-hover:text-indigo-500 dark:text-gray-600" />
                    </a>
                @endforeach

                <button type="button"
                    @if ($overdueTotal > 0)
                        x-on:click="drillDown(@js(['bucket' => 'over10']), @js(__('All assignees').' · '.$buckets['over10']['label']))"
                    @else
                        disabled
                    @endif
                    class="group flex items-center gap-4 rounded-xl p-5 text-start shadow-sm ring-1 transition disabled:cursor-default {{ $overdueTotal > 0 ? 'bg-red-50 ring-red-600/20 hover:shadow-md dark:bg-red-500/10 dark:ring-red-500/30' : 'bg-white ring-gray-900/5 dark:bg-gray-800 dark:ring-white/10' }}">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $overdueTotal > 0 ? 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                        <x-icon name="exclamation-triangle" class="h-6 w-6" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium {{ $overdueTotal > 0 ? 'text-red-700 dark:text-red-300' : 'text-gray-500 dark:text-gray-400' }}">{{ __('Needs Attention') }}</p>
                        <p class="text-2xl font-bold tabular-nums {{ $overdueTotal > 0 ? 'text-red-700 dark:text-red-300' : 'text-gray-900 dark:text-gray-100' }}">{{ number_format($overdueTotal) }}</p>
                        <p class="truncate text-xs {{ $overdueTotal > 0 ? 'text-red-600/80 dark:text-red-400/80' : 'text-gray-500 dark:text-gray-400' }}">{{ __('In progress for 10 days or more') }}</p>
                    </div>
                    @if ($overdueTotal > 0)
                        <x-icon name="arrow-right" class="ms-auto h-4 w-4 shrink-0 text-red-400 transition group-hover:translate-x-0.5" />
                    @endif
                </button>
            </div>

            {{-- Breakdown by year and assignee --}}
            <x-card :title="__('In-Progress Projects by Assignee')" icon="users"
                :description="__('Currently assigned projects awaiting completion, grouped by year and their current assignee, and how many days it has been since assignment.')">
                @if ($rows->isNotEmpty() && auth()->user()->can(\App\Enums\UserPermission::ExportDashboard->value))
                    <x-slot name="actions">
                        <x-button-link :href="route('dashboard.export')">
                            <x-icon name="arrow-down-tray" class="h-4 w-4" />
                            {{ __('Export to Excel') }}
                        </x-button-link>
                    </x-slot>
                @endif

                @if ($rows->isNotEmpty())
                    {{-- Ageing distribution --}}
                    <div class="px-5 pt-5 sm:px-6">
                        <div class="flex h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700" role="img"
                            aria-label="{{ collect($buckets)->map(fn ($bucket, $key) => $bucket['label'].': '.$rows->sum($key))->join(', ') }}">
                            @foreach ($buckets as $bucket => $style)
                                @if ($rows->sum($bucket) > 0)
                                    <div class="{{ $style['bar'] }} h-full" style="width: {{ round($rows->sum($bucket) / $inProgressTotal * 100, 2) }}%"></div>
                                @endif
                            @endforeach
                        </div>
                        <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-sm">
                            @foreach ($buckets as $bucket => $style)
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $style['bar'] }}" aria-hidden="true"></span>
                                    {{ $style['label'] }}
                                    <span class="font-semibold tabular-nums text-gray-900 dark:text-gray-100">{{ $rows->sum($bucket) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="overflow-x-auto p-5 sm:p-6">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th scope="col" class="py-3 pe-4 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Year') }}</th>
                                <th scope="col" class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Assignee') }}</th>
                                @foreach ($buckets as $style)
                                    <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $style['label'] }}</th>
                                @endforeach
                                <th scope="col" class="ps-4 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @forelse ($rows as $row)
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/30">
                                    @if ($row['yearRowspan'])
                                        <td rowspan="{{ $row['yearRowspan'] }}"
                                            class="py-3 pe-4 align-top whitespace-nowrap border-e border-gray-100 dark:border-gray-700/60 bg-white dark:bg-gray-800">
                                            <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-sm font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ $row['year'] }}</span>
                                        </td>
                                    @endif
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            @if ($row['assignee'])
                                                <x-avatar :name="$row['assignee']->name" />
                                            @endif
                                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $row['assignee']?->name ?? __('Unassigned') }}</span>
                                        </div>
                                    </td>
                                    @foreach ($buckets as $bucket => $style)
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            @if ($row[$bucket] > 0)
                                                <button type="button"
                                                    x-on:click="drillDown(@js(array_filter(['bucket' => $bucket, 'year' => $row['year'], 'assignee_id' => $row['assignee']?->id])), @js($row['year'].' · '.($row['assignee']?->name ?? __('Unassigned')).' · '.$style['label']))"
                                                    title="{{ __('Show projects') }}"
                                                    class="inline-flex min-w-[2.5rem] justify-center rounded-full px-2.5 py-1 text-sm font-semibold tabular-nums ring-1 ring-inset transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 {{ $style['pill'] }}">
                                                    {{ $row[$bucket] }}
                                                </button>
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600">0</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="ps-4 py-3 whitespace-nowrap text-end font-semibold tabular-nums text-gray-900 dark:text-gray-100">{{ $row['total'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500">
                                            <x-icon name="inbox" class="h-6 w-6" />
                                        </div>
                                        <p class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('No in-progress projects.') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($rows->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 dark:border-gray-700 font-semibold">
                                    <td colspan="2" class="py-3 pe-4 whitespace-nowrap text-gray-900 dark:text-gray-100">{{ __('Total') }}</td>
                                    @foreach ($buckets as $bucket => $style)
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            @if ($rows->sum($bucket) > 0)
                                                <button type="button"
                                                    x-on:click="drillDown(@js(['bucket' => $bucket]), @js(__('All assignees').' · '.$style['label']))"
                                                    title="{{ __('Show projects') }}"
                                                    class="inline-flex min-w-[2.5rem] justify-center rounded-full px-2.5 py-1 text-sm font-semibold tabular-nums text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-500/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                                                    {{ $rows->sum($bucket) }}
                                                </button>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">0</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="ps-4 py-3 whitespace-nowrap text-end tabular-nums text-gray-900 dark:text-gray-100">{{ $inProgressTotal }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </x-card>
        </div>

        <x-modal name="dashboard-projects" maxWidth="4xl">
            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">{{ __('In Progress') }}</p>
                    <h2 class="mt-0.5 text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="title"></h2>
                </div>
                <button type="button" x-on:click="$dispatch('close')" aria-label="{{ __('Close') }}"
                    class="-me-2 rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                    <x-icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="p-6">
                <div x-show="loading" class="flex items-center justify-center gap-3 py-10 text-sm text-gray-500 dark:text-gray-400">
                    <svg class="h-5 w-5 animate-spin text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    {{ __('Loading...') }}
                </div>
                <div x-show="failed" style="display: none;">
                    <x-alert type="error">{{ __('Could not load the projects. Please try again.') }}</x-alert>
                </div>
                <div x-show="! loading && ! failed" x-html="html"></div>
            </div>
        </x-modal>
    </div>
</x-app-layout>
