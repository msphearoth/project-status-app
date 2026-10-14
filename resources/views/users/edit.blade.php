<x-app-layout>
    <x-slot name="header">
        <x-page-heading :title="__('Edit User')" :description="__('Update :name\'s details, role and the features they can use.', ['name' => $user->name])" :back="route('users.index')" />
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('users._form', [
                'action' => route('users.update', $user),
                'method' => 'PUT',
                'submitLabel' => __('Save Changes'),
                'submitIcon' => 'check',
            ])
        </div>
    </div>
</x-app-layout>
