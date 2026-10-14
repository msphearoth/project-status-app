<x-app-layout>
    <x-slot name="header">
        <x-page-heading :title="__('New User')" :description="__('Create an account so a team member can sign in.')" :back="route('users.index')" />
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('users._form', [
                'action' => route('users.store'),
                'method' => 'POST',
                'submitLabel' => __('Create User'),
                'submitIcon' => 'user-plus',
            ])
        </div>
    </div>
</x-app-layout>
