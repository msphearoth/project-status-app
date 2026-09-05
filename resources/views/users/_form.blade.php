@php
    $user = $user ?? null;
    $selectedRole = old('role', $user?->role?->value ?? \App\Enums\UserRole::User->value);
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
            :value="old('name', $user?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
            :value="old('email', $user?->email)" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="role" :value="__('Role')" />
        <select id="role" name="role" required
            @if ($isLastAdmin ?? false) disabled @endif
            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
            @foreach (\App\Enums\UserRole::cases() as $role)
                <option value="{{ $role->value }}" @selected($selectedRole === $role->value)>
                    {{ $role->label() }}
                </option>
            @endforeach
        </select>
        @if ($isLastAdmin ?? false)
            <input type="hidden" name="role" value="{{ \App\Enums\UserRole::Admin->value }}">
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('This is the last admin, so the role cannot be changed.') }}</p>
        @endif
        <x-input-error :messages="$errors->get('role')" class="mt-2" />
    </div>

    <div></div>

    <div>
        <x-input-label for="password" :value="$user ? __('New Password (optional)') : __('Password')" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
            :required="! $user" autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full"
            :required="! $user" autocomplete="new-password" />
    </div>
</div>
