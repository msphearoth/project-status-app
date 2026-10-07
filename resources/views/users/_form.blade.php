@php
    $user = $user ?? null;
    $isLastAdmin = $isLastAdmin ?? false;
    $selectedRole = old('role', $user?->role?->value ?? \App\Enums\UserRole::User->value);
    $roleDescriptions = [
        \App\Enums\UserRole::Admin->value => __('Full access, including managing users and every project.'),
        \App\Enums\UserRole::User->value => __('Can create projects and work on the projects assigned to them.'),
    ];
@endphp

<div class="space-y-6">
    {{-- Account --}}
    <div class="grid grid-cols-1 gap-x-8 gap-y-4 lg:grid-cols-3">
        <div class="flex items-start gap-3 lg:pt-5">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="user" class="h-5 w-5" />
            </span>
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Account') }}</h3>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ __('The name shown in the app and the email used to sign in.') }}</p>
            </div>
        </div>

        <x-card class="lg:col-span-2">
            <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6">
                <div>
                    <x-input-label for="name" :value="__('Name')" required />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                        :value="old('name', $user?->name)" required autofocus autocomplete="off" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Email')" required />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                        :value="old('email', $user?->email)" required autocomplete="off" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
            </div>
        </x-card>
    </div>

    {{-- Role --}}
    <div class="grid grid-cols-1 gap-x-8 gap-y-4 lg:grid-cols-3">
        <div class="flex items-start gap-3 lg:pt-5">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="shield-check" class="h-5 w-5" />
            </span>
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Role') }}</h3>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ __('Decides what this person is allowed to do.') }}</p>
            </div>
        </div>

        <x-card class="lg:col-span-2">
            <fieldset class="p-5 sm:p-6">
                <legend class="sr-only">{{ __('Role') }}</legend>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach (\App\Enums\UserRole::cases() as $role)
                        <label class="relative flex cursor-pointer gap-3 rounded-xl border border-gray-200 p-4 transition hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/60 has-[:checked]:ring-1 has-[:checked]:ring-indigo-500 has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-70 dark:border-gray-700 dark:hover:border-indigo-500/50 dark:has-[:checked]:bg-indigo-500/10">
                            <input type="radio" name="role" value="{{ $role->value }}" required
                                @checked($selectedRole === $role->value)
                                @disabled($isLastAdmin)
                                class="mt-0.5 h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:focus:ring-offset-gray-800">
                            <span>
                                <span class="block text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $role->label() }}</span>
                                <span class="mt-0.5 block text-sm text-gray-500 dark:text-gray-400">{{ $roleDescriptions[$role->value] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @if ($isLastAdmin)
                    <input type="hidden" name="role" value="{{ \App\Enums\UserRole::Admin->value }}">
                    <p class="mt-3 flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                        <x-icon name="information-circle" class="h-4 w-4 shrink-0" />
                        {{ __('This is the last admin, so the role cannot be changed.') }}
                    </p>
                @endif
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </fieldset>
        </x-card>
    </div>

    {{-- Password --}}
    <div class="grid grid-cols-1 gap-x-8 gap-y-4 lg:grid-cols-3">
        <div class="flex items-start gap-3 lg:pt-5">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="key" class="h-5 w-5" />
            </span>
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('Password') }}</h3>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                    {{ $user ? __('Leave blank to keep the current password.') : __('Choose a password for the new account.') }}
                </p>
            </div>
        </div>

        <x-card class="lg:col-span-2">
            <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6" x-data="{ visible: false }">
                <div>
                    <x-input-label for="password" :value="$user ? __('New Password (optional)') : __('Password')" :required="! $user" />
                    <x-text-input id="password" name="password" type="password" x-bind:type="visible ? 'text' : 'password'" class="mt-1 block w-full"
                        :required="! $user" autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" :required="! $user" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" x-bind:type="visible ? 'text' : 'password'" class="mt-1 block w-full"
                        :required="! $user" autocomplete="new-password" />
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 sm:col-span-2">
                    <input type="checkbox" x-model="visible" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:focus:ring-offset-gray-800">
                    {{ __('Show passwords') }}
                </label>
            </div>
        </x-card>
    </div>
</div>
