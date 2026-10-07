<x-app-layout>
    <x-slot name="header">
        <x-page-heading :title="__('Users')" :description="__('Manage who can sign in and what they are allowed to do.')">
            @can('create', \App\Models\User::class)
                <x-button-link :href="route('users.create')" variant="primary">
                    <x-icon name="user-plus" class="h-4 w-4" />
                    {{ __('New User') }}
                </x-button-link>
            @endcan
        </x-page-heading>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <x-alert>{{ session('status') }}</x-alert>
            @endif

            @if (session('error'))
                <x-alert type="error">{{ session('error') }}</x-alert>
            @endif

            <x-card>
                <div class="flex items-center justify-between gap-4 px-5 py-4 sm:px-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ trans_choice(':count user|:count users', $users->total()) }}
                    </p>
                </div>

                <div class="overflow-x-auto border-t border-gray-100 dark:border-gray-700">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th scope="col" class="ps-5 sm:ps-6 pe-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Name') }}</th>
                                <th scope="col" class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Role') }}</th>
                                <th scope="col" class="hidden md:table-cell px-4 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Created Projects') }}</th>
                                <th scope="col" class="hidden md:table-cell px-4 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Assigned Projects') }}</th>
                                <th scope="col" class="pe-5 sm:pe-6 py-3"><span class="sr-only">{{ __('Actions') }}</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @foreach ($users as $user)
                                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="ps-5 sm:ps-6 pe-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <x-avatar :name="$user->name" size="lg" />
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</span>
                                                    @if ($user->id === auth()->id())
                                                        <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">{{ __('You') }}</span>
                                                    @endif
                                                </div>
                                                <div class="truncate text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $user->role->badgeClass() }}">
                                            @if ($user->role === \App\Enums\UserRole::Admin)
                                                <x-icon name="shield-check" class="h-3.5 w-3.5" />
                                            @endif
                                            {{ $user->role->label() }}
                                        </span>
                                    </td>
                                    <td class="hidden md:table-cell px-4 py-4 whitespace-nowrap text-end tabular-nums text-gray-700 dark:text-gray-300">{{ $user->created_projects_count }}</td>
                                    <td class="hidden md:table-cell px-4 py-4 whitespace-nowrap text-end tabular-nums text-gray-700 dark:text-gray-300">{{ $user->assigned_projects_count }}</td>
                                    <td class="pe-5 sm:pe-6 py-4 whitespace-nowrap text-end">
                                        <div class="inline-flex items-center gap-1">
                                            @can('update', $user)
                                                <a href="{{ route('users.edit', $user) }}" title="{{ __('Edit') }}"
                                                   class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                                                    <x-icon name="pencil-square" class="h-4 w-4" />
                                                    <span class="hidden sm:inline">{{ __('Edit') }}</span>
                                                </a>
                                            @endcan
                                            @can('delete', $user)
                                                <button type="button" title="{{ __('Delete') }}"
                                                    x-data=""
                                                    x-on:click="$dispatch('open-modal', 'confirm-user-deletion-{{ $user->id }}')"
                                                    class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                                                    <x-icon name="trash" class="h-4 w-4" />
                                                    <span class="hidden sm:inline">{{ __('Delete') }}</span>
                                                </button>

                                                <x-modal :name="'confirm-user-deletion-'.$user->id" maxWidth="lg" focusable>
                                                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="text-start whitespace-normal">
                                                        @csrf
                                                        @method('DELETE')

                                                        <div class="flex gap-4 p-6">
                                                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                                                                <x-icon name="exclamation-triangle" class="h-6 w-6" />
                                                            </span>
                                                            <div>
                                                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                                    {{ __('Are you sure you want to delete :name?', ['name' => $user->name]) }}
                                                                </h2>
                                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                                    {{ __('This action cannot be undone.') }}
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
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-700 sm:px-6">
                        {{ $users->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>
