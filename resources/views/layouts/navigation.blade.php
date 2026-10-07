@php
    $navigationLinks = array_filter([
        ['route' => 'dashboard', 'label' => __('Dashboard'), 'icon' => 'squares', 'active' => request()->routeIs('dashboard')],
        ['route' => 'projects.index', 'label' => __('Projects'), 'icon' => 'folder', 'active' => request()->routeIs('projects.*') && ! request()->routeIs('projects.create')],
        Auth::user()->can('create', \App\Models\Project::class)
            ? ['route' => 'projects.create', 'label' => __('New Project'), 'icon' => 'plus', 'active' => request()->routeIs('projects.create')]
            : null,
        Auth::user()->can('viewAny', \App\Models\User::class)
            ? ['route' => 'users.index', 'label' => __('Users'), 'icon' => 'users', 'active' => request()->routeIs('users.*')]
            : null,
    ]);
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-white/90 dark:bg-gray-800/90 backdrop-blur border-b border-gray-200 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="shrink-0 flex items-center">
                    <x-application-logo class="block h-10 w-auto" />
                </a>

                <!-- Navigation Links -->
                <div class="hidden md:flex md:items-center md:gap-1">
                    @foreach ($navigationLinks as $link)
                        <x-nav-link :href="route($link['route'])" :active="$link['active']">
                            <x-icon :name="$link['icon']" class="h-5 w-5" />
                            {{ $link['label'] }}
                        </x-nav-link>
                    @endforeach
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden md:flex md:items-center md:gap-3">
                <x-language-switcher />

                <x-notification-bell />

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded-full py-1 ps-1 pe-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition ease-in-out duration-150">
                            <x-avatar :name="Auth::user()->name" />
                            <span class="max-w-[10rem] truncate">{{ Auth::user()->name }}</span>
                            <x-icon name="chevron-down" class="h-4 w-4 text-gray-400" />
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-600">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->email }}</div>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center gap-1 md:hidden">
                <x-notification-bell />

                <button @click="open = ! open" :aria-expanded="open" aria-label="{{ __('Menu') }}" class="inline-flex items-center justify-center p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden md:hidden border-t border-gray-200 dark:border-gray-700">
        <div class="px-4 py-3 space-y-1">
            @foreach ($navigationLinks as $link)
                <x-responsive-nav-link :href="route($link['route'])" :active="$link['active']">
                    <x-icon :name="$link['icon']" class="h-5 w-5" />
                    {{ $link['label'] }}
                </x-responsive-nav-link>
            @endforeach
        </div>

        <!-- Responsive Settings Options -->
        <div class="px-4 pt-4 pb-3 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3 px-3">
                <x-avatar :name="Auth::user()->name" size="lg" />
                <div class="min-w-0">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200 truncate">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500 truncate">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="px-3 mt-3">
                <x-language-switcher />
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
