{{--
    Create/edit user form: account details, role and per-feature permissions,
    and password, with a live access summary beside it.

    Expects: $action, $method, $submitLabel, $submitIcon; optional $user and $isLastAdmin.
--}}
@use('App\Enums\PermissionModule')
@use('App\Enums\UserPermission')
@use('App\Enums\UserRole')
@php
    $user = $user ?? null;
    $isLastAdmin = $isLastAdmin ?? false;
    $selectedRole = old('role', $user?->role?->value ?? UserRole::User->value);
    $defaultPermissions = UserPermission::valuesOf(UserPermission::standardUserDefaults());
    $selectedPermissions = match (true) {
        session()->hasOldInput() => old('permissions', []),
        $user !== null && ! $user->isAdmin() => $user->getPermissionNames()->all(),
        default => $defaultPermissions,
    };
    $sensitivePermissions = array_values(array_filter(UserPermission::cases(), fn (UserPermission $permission) => $permission->isSensitive()));

    $formConfig = [
        'name' => old('name', $user?->name ?? ''),
        'email' => old('email', $user?->email ?? ''),
        'role' => $selectedRole,
        'selected' => array_values($selectedPermissions),
        'defaults' => $defaultPermissions,
        'all' => UserPermission::valuesOf(UserPermission::cases()),
        'sensitive' => UserPermission::valuesOf($sensitivePermissions),
        'adminRole' => UserRole::Admin->value,
    ];

    $presets = [
        'recommended' => __('Recommended'),
        'all' => __('All'),
        'none' => __('None'),
    ];

    $sectionNumberClasses = 'flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white shadow-sm shadow-indigo-600/30';
    $iconInputClasses = 'pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-gray-400 dark:text-gray-500';
@endphp

<form method="POST" action="{{ $action }}"
    x-data="userForm(@js($formConfig))"
    x-on:input="dirty = true"
    x-on:submit="submitting = true"
    x-on:beforeunload.window="warnBeforeLeaving($event)">
    @csrf
    @method($method)

    @if ($errors->any())
        <x-alert type="error" class="mb-6">{{ __('Some details need your attention. Check the highlighted fields below.') }}</x-alert>
    @endif

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        {{-- Main column --}}
        <div class="space-y-6">

            {{-- 1. Account --}}
            <x-card>
                <div class="flex items-start gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-700 sm:px-6">
                    <span class="{{ $sectionNumberClasses }}">1</span>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Account details') }}</h3>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ __('The name shown in the app and the email used to sign in.') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6">
                    <div>
                        <x-input-label for="name" :value="__('Full name')" required />
                        <div class="relative mt-1.5">
                            <span class="{{ $iconInputClasses }}"><x-icon name="user" class="h-5 w-5" /></span>
                            <x-text-input id="name" name="name" type="text" x-model="name" class="block w-full ps-10"
                                :value="old('name', $user?->name)" required autofocus autocomplete="off"
                                :placeholder="__('e.g. Sok Dara')" />
                        </div>
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email address')" required />
                        <div class="relative mt-1.5">
                            <span class="{{ $iconInputClasses }}"><x-icon name="envelope" class="h-5 w-5" /></span>
                            <x-text-input id="email" name="email" type="email" x-model="email" class="block w-full ps-10"
                                :value="old('email', $user?->email)" required autocomplete="off"
                                placeholder="name@example.com" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                </div>
            </x-card>

            {{-- 2. Role & access --}}
            <x-card id="permissions" class="scroll-mt-24">
                <div class="flex items-start gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-700 sm:px-6">
                    <span class="{{ $sectionNumberClasses }}">2</span>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Role & access') }}</h3>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ __('Pick a role, then fine-tune exactly which features this person can use.') }}</p>
                    </div>
                </div>

                {{-- Role --}}
                <fieldset class="p-5 sm:p-6" aria-labelledby="role-heading">
                    <p id="role-heading" class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Role') }}</p>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach (UserRole::cases() as $role)
                            @php($isAdminRole = $role === UserRole::Admin)
                            <label @class([
                                'group relative flex cursor-pointer gap-4 rounded-xl border-2 p-4 transition',
                                'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600',
                                'has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-60',
                                'has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50/70 dark:has-[:checked]:border-violet-500 dark:has-[:checked]:bg-violet-500/10' => $isAdminRole,
                                'has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/70 dark:has-[:checked]:border-indigo-500 dark:has-[:checked]:bg-indigo-500/10' => ! $isAdminRole,
                            ])>
                                <input type="radio" name="role" value="{{ $role->value }}" required x-model="role"
                                    @checked($selectedRole === $role->value)
                                    @disabled($isLastAdmin)
                                    class="peer sr-only">

                                <span @class([
                                    'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition',
                                    'bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300' => $isAdminRole,
                                    'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300' => ! $isAdminRole,
                                ])>
                                    <x-icon :name="$role->icon()" class="h-6 w-6" />
                                </span>

                                <span class="min-w-0 flex-1 pe-6">
                                    <span class="block text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $role->label() }}</span>
                                    <span class="mt-1 block text-sm leading-snug text-gray-500 dark:text-gray-400">{{ $role->description() }}</span>
                                </span>

                                {{-- Selection indicator --}}
                                <span class="absolute end-4 top-4 flex h-5 w-5 items-center justify-center rounded-full border-2 border-gray-300 text-white transition peer-checked:border-transparent dark:border-gray-600 {{ $isAdminRole ? 'peer-checked:bg-violet-600' : 'peer-checked:bg-indigo-600' }} peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500 peer-focus-visible:ring-offset-2 dark:peer-focus-visible:ring-offset-gray-800">
                                    <x-icon name="check" class="!h-3 !w-3 stroke-[3]" />
                                </span>
                            </label>
                        @endforeach
                    </div>

                    @if ($isLastAdmin)
                        <input type="hidden" name="role" value="{{ UserRole::Admin->value }}">
                        <p class="mt-3 flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
                            <x-icon name="lock-closed" class="h-4 w-4 shrink-0" />
                            {{ __('This is the last admin, so the role cannot be changed.') }}
                        </p>
                    @endif
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </fieldset>

                <div class="border-t border-gray-100 dark:border-gray-700"></div>

                {{-- Permissions: admin --}}
                <div x-show="isAdmin" @style(['display: none' => $selectedRole !== UserRole::Admin->value]) class="p-5 sm:p-6">
                    <div class="overflow-hidden rounded-xl bg-gradient-to-br from-violet-600 to-indigo-600 p-5 text-white shadow-sm">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white/15 ring-1 ring-inset ring-white/20">
                                <x-icon name="shield-check" class="h-6 w-6" />
                            </span>
                            <div>
                                <p class="font-semibold">{{ __('Full access') }}</p>
                                <p class="mt-0.5 text-sm text-violet-100">{{ __('Admins can use every feature below and manage users. Switch to Standard User to choose features one by one.') }}</p>
                            </div>
                        </div>

                        <ul class="mt-4 grid grid-cols-1 gap-x-4 gap-y-2 text-sm sm:grid-cols-2">
                            @foreach (UserPermission::cases() as $permission)
                                <li class="flex items-center gap-2 text-violet-50">
                                    <x-icon name="check-circle" class="h-4 w-4 shrink-0 text-emerald-300" />
                                    {{ $permission->label() }}
                                </li>
                            @endforeach
                            <li class="flex items-center gap-2 text-violet-50">
                                <x-icon name="check-circle" class="h-4 w-4 shrink-0 text-emerald-300" />
                                {{ __('Manage users and permissions') }}
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Permissions: standard user --}}
                <fieldset x-show="! isAdmin" @style(['display: none' => $selectedRole === UserRole::Admin->value]) class="space-y-5 p-5 sm:p-6">
                    <legend class="sr-only">{{ __('Permissions') }}</legend>

                    {{-- Toolbar: presets and progress --}}
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Permissions') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <span class="font-semibold tabular-nums text-gray-900 dark:text-gray-100" x-text="enabledCount"></span>
                                {{ __('of') }}
                                <span class="tabular-nums" x-text="all.length"></span>
                                {{ __('features enabled') }}
                            </p>
                        </div>

                        <div class="inline-flex shrink-0 rounded-lg bg-gray-100 p-1 dark:bg-gray-900/60" role="group" aria-label="{{ __('Quick presets') }}">
                            @foreach ($presets as $preset => $label)
                                <button type="button" x-on:click="applyPreset(@js($preset))"
                                    x-bind:aria-pressed="preset === @js($preset)"
                                    x-bind:class="preset === @js($preset)
                                        ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-gray-100'
                                        : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100'"
                                    class="rounded-md px-3 py-1.5 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700" aria-hidden="true">
                        <div class="h-full rounded-full bg-indigo-600 transition-all duration-300" x-bind:style="`width: ${enabledPercent}%`"></div>
                    </div>

                    {{-- Modules --}}
                    @foreach (PermissionModule::cases() as $module)
                        @php($groupValues = UserPermission::valuesOf($module->permissions()))
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700" x-data="{ group: @js($groupValues) }">
                            <div class="flex items-center gap-3 bg-gray-50/80 px-4 py-3 dark:bg-gray-900/40">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-gray-600 shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-800 dark:text-gray-300 dark:ring-white/10">
                                    <x-icon :name="$module->icon()" class="h-5 w-5" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $module->label() }}
                                        <span class="rounded-full bg-gray-200/70 px-2 py-0.5 text-xs font-medium tabular-nums text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            x-text="countIn(group) + ' / ' + group.length"></span>
                                    </p>
                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $module->description() }}</p>
                                </div>
                                <button type="button" role="switch" x-on:click="toggleGroup(group)"
                                    x-bind:aria-checked="countIn(group) === group.length"
                                    aria-label="{{ __('Turn on every :module permission', ['module' => $module->label()]) }}"
                                    x-bind:class="countIn(group) === group.length ? 'bg-indigo-600' : (countIn(group) > 0 ? 'bg-indigo-300 dark:bg-indigo-500/50' : 'bg-gray-200 dark:bg-gray-600')"
                                    class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800">
                                    <span aria-hidden="true" class="absolute start-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition"
                                        x-bind:class="countIn(group) === group.length && 'translate-x-5 rtl:-translate-x-5'"></span>
                                </button>
                            </div>

                            <ul class="divide-y divide-gray-100 dark:divide-gray-700/70">
                                @foreach ($module->permissions() as $permission)
                                    <li>
                                        <label class="flex cursor-pointer items-start gap-4 px-4 py-3.5 transition hover:bg-gray-50 has-[:checked]:bg-indigo-50/50 dark:hover:bg-gray-700/30 dark:has-[:checked]:bg-indigo-500/5">
                                            <span class="min-w-0 flex-1">
                                                <span class="flex flex-wrap items-center gap-2">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $permission->label() }}</span>
                                                    @if ($permission->isSensitive())
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20">
                                                            <x-icon name="exclamation-triangle" class="!h-3 !w-3" />
                                                            {{ __('Sensitive') }}
                                                        </span>
                                                    @endif
                                                </span>
                                                <span class="mt-0.5 block text-sm text-gray-500 dark:text-gray-400">{{ $permission->description() }}</span>
                                            </span>

                                            <span class="relative mt-0.5 inline-flex shrink-0">
                                                <input type="checkbox" role="switch" name="permissions[]" value="{{ $permission->value }}"
                                                    x-model="selected" x-bind:disabled="isAdmin"
                                                    @checked(in_array($permission->value, $selectedPermissions, true))
                                                    class="peer sr-only">
                                                <span aria-hidden="true" class="h-6 w-11 rounded-full bg-gray-200 transition peer-checked:bg-indigo-600 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500 peer-focus-visible:ring-offset-2 dark:bg-gray-600 dark:peer-focus-visible:ring-offset-gray-800"></span>
                                                <span aria-hidden="true" class="absolute start-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
                                            </span>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach

                    <p class="flex items-start gap-2 rounded-lg bg-sky-50 px-3 py-2.5 text-sm text-sky-800 dark:bg-sky-500/10 dark:text-sky-300">
                        <x-icon name="information-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                        {{ __('Users can always view, edit and complete the projects assigned to them. Managing users is reserved for admins.') }}
                    </p>
                    <x-input-error :messages="\Illuminate\Support\Arr::flatten($errors->get('permissions*'))" />
                </fieldset>
            </x-card>

            {{-- 3. Password --}}
            <x-card>
                <div class="flex items-start gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-700 sm:px-6">
                    <span class="{{ $sectionNumberClasses }}">3</span>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Password') }}</h3>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                            {{ $user ? __('Leave blank to keep the current password.') : __('Choose a password for the new account.') }}
                        </p>
                    </div>
                    <button type="button" x-on:click="generatePassword()"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm font-medium text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-500/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                        <x-icon name="sparkles" class="h-4 w-4" />
                        {{ __('Generate') }}
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6">
                    @foreach ([
                        ['id' => 'password', 'ref' => 'password', 'label' => $user ? __('New password') : __('Password')],
                        ['id' => 'password_confirmation', 'ref' => 'passwordConfirmation', 'label' => __('Confirm password')],
                    ] as $field)
                        <div>
                            <x-input-label :for="$field['id']" :value="$field['label']" :required="! $user" />
                            <div class="relative mt-1.5">
                                <span class="{{ $iconInputClasses }}"><x-icon name="key" class="h-5 w-5" /></span>
                                <x-text-input :id="$field['id']" :name="$field['id']" type="password" x-ref="{{ $field['ref'] }}"
                                    x-bind:type="showPassword ? 'text' : 'password'" class="block w-full ps-10 pe-10 font-mono"
                                    :required="! $user" autocomplete="new-password" />
                                <button type="button" x-on:click="showPassword = ! showPassword"
                                    x-bind:aria-label="showPassword ? @js(__('Hide password')) : @js(__('Show password'))"
                                    class="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-400 hover:text-gray-600 focus:outline-none focus-visible:text-indigo-600 dark:hover:text-gray-300">
                                    <span x-show="! showPassword"><x-icon name="eye" class="h-5 w-5" /></span>
                                    <span x-show="showPassword" style="display: none;"><x-icon name="eye-slash" class="h-5 w-5" /></span>
                                </button>
                            </div>
                            @if ($field['id'] === 'password')
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-card>

            {{-- Actions --}}
            <div class="sticky bottom-0 z-10 -mx-4 border-t border-gray-200 bg-white/90 px-4 py-3 backdrop-blur dark:border-gray-700 dark:bg-gray-800/90 sm:mx-0 sm:rounded-xl sm:border sm:shadow-lg">
                <div class="flex items-center justify-between gap-3">
                    <p class="flex min-w-0 items-center gap-2 text-sm">
                        <span x-show="dirty" style="display: none;" class="inline-flex items-center gap-2 font-medium text-amber-700 dark:text-amber-400">
                            <span class="h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
                            <span class="truncate">{{ __('Unsaved changes') }}</span>
                        </span>
                        <span x-show="! dirty" class="hidden truncate text-gray-500 dark:text-gray-400 sm:inline">{{ __('No changes yet') }}</span>
                    </p>
                    <div class="flex shrink-0 gap-2">
                        <x-button-link :href="route('users.index')" class="!px-3 sm:!px-4">
                            {{ __('Cancel') }}
                        </x-button-link>
                        <x-primary-button x-bind:disabled="submitting" class="!px-3 disabled:opacity-70 sm:!px-4">
                            <x-icon :name="$submitIcon" class="h-4 w-4" />
                            {{ $submitLabel }}
                        </x-primary-button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Live summary --}}
        <aside class="space-y-4 lg:sticky lg:top-24">
            <x-card class="overflow-hidden">
                <div class="h-16 bg-gradient-to-r" x-bind:class="isAdmin ? 'from-violet-600 to-fuchsia-500' : 'from-indigo-500 to-sky-500'"></div>
                <div class="-mt-8 px-5 pb-5">
                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-white text-xl font-bold text-indigo-700 shadow-md ring-4 ring-white dark:bg-gray-700 dark:text-indigo-300 dark:ring-gray-800"
                        x-text="initials">{{ $user ? mb_strtoupper(mb_substr($user->name, 0, 1)) : '?' }}</span>
                    <p class="mt-3 truncate text-base font-semibold text-gray-900 dark:text-gray-100"
                        x-text="name || @js(__('New user'))">{{ $user?->name ?? __('New user') }}</p>
                    <p class="truncate text-sm text-gray-500 dark:text-gray-400" x-text="email || '—'">{{ $user?->email ?? '—' }}</p>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach (UserRole::cases() as $role)
                            <span x-show="role === @js($role->value)" @style(['display: none' => $selectedRole !== $role->value])
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $role->badgeClass() }}">
                                <x-icon :name="$role->icon()" class="!h-3.5 !w-3.5" />
                                {{ $role->label() }}
                            </span>
                        @endforeach
                        @if ($user?->is(auth()->user()))
                            <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">{{ __('You') }}</span>
                        @endif
                    </div>
                </div>

                {{-- Access overview --}}
                <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-700">
                    <div class="flex items-baseline justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Access') }}</p>
                        <p class="text-sm tabular-nums text-gray-500 dark:text-gray-400">
                            <span class="text-lg font-bold text-gray-900 dark:text-gray-100" x-text="enabledCount"></span> / <span x-text="all.length"></span>
                        </p>
                    </div>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700" aria-hidden="true">
                        <div class="h-full rounded-full transition-all duration-300" x-bind:class="isAdmin ? 'bg-violet-600' : 'bg-indigo-600'" x-bind:style="`width: ${enabledPercent}%`"></div>
                    </div>

                    <ul class="mt-4 space-y-1.5 text-sm">
                        @foreach (UserPermission::cases() as $permission)
                            <li class="flex items-center gap-2" x-bind:class="isOn(@js($permission->value)) ? 'text-gray-700 dark:text-gray-200' : 'text-gray-400 line-through decoration-gray-300 dark:text-gray-500 dark:decoration-gray-600'">
                                <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full"
                                    x-bind:class="isOn(@js($permission->value)) ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-700'">
                                    <span x-show="isOn(@js($permission->value))"><x-icon name="check" class="!h-2.5 !w-2.5 stroke-[3]" /></span>
                                </span>
                                <span class="truncate">{{ $permission->label() }}</span>
                            </li>
                        @endforeach
                        <li class="flex items-center gap-2" x-bind:class="isAdmin ? 'text-gray-700 dark:text-gray-200' : 'text-gray-400 line-through decoration-gray-300 dark:text-gray-500 dark:decoration-gray-600'">
                            <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full"
                                x-bind:class="isAdmin ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-700'">
                                <span x-show="isAdmin"><x-icon name="check" class="!h-2.5 !w-2.5 stroke-[3]" /></span>
                            </span>
                            <span class="truncate">{{ __('Manage users and permissions') }}</span>
                        </li>
                    </ul>

                    <p x-show="! isAdmin && sensitiveCount > 0" style="display: none;"
                        class="mt-4 flex items-start gap-2 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
                        <x-icon name="exclamation-triangle" class="h-4 w-4 shrink-0" />
                        <span x-text="@js(__(':count sensitive permission(s) enabled. Double-check these are needed.')).replace(':count', sensitiveCount)"></span>
                    </p>
                </div>

                @if ($user)
                    <dl class="grid grid-cols-3 divide-x divide-gray-100 border-t border-gray-100 text-center dark:divide-gray-700 dark:border-gray-700 rtl:divide-x-reverse">
                        <div class="px-2 py-3">
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Created') }}</dt>
                            <dd class="mt-0.5 text-base font-semibold tabular-nums text-gray-900 dark:text-gray-100">{{ $user->created_projects_count }}</dd>
                        </div>
                        <div class="px-2 py-3">
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Assigned') }}</dt>
                            <dd class="mt-0.5 text-base font-semibold tabular-nums text-gray-900 dark:text-gray-100">{{ $user->assigned_projects_count }}</dd>
                        </div>
                        <div class="px-2 py-3">
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Joined') }}</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $user->created_at?->format('M Y') ?? '—' }}</dd>
                        </div>
                    </dl>
                @endif
            </x-card>
        </aside>
    </div>
</form>
