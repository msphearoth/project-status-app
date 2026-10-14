{{-- Confirmation for reverting a completed project to in progress. Open it with
     $dispatch('confirm-project-reopen', { url, code }). --}}
<div x-data="{ reopenUrl: '', projectCode: '' }"
    x-on:confirm-project-reopen.window="reopenUrl = $event.detail.url; projectCode = $event.detail.code; $dispatch('open-modal', 'confirm-project-reopen')">
    <x-modal name="confirm-project-reopen" maxWidth="lg" focusable>
        <form method="POST" x-bind:action="reopenUrl" class="text-start whitespace-normal">
            @csrf

            <div class="flex gap-4 p-6">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                    <x-icon name="arrow-uturn-left" class="h-6 w-6" />
                </span>
                <div class="min-w-0 flex-1">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('Revert this project to In Progress?') }}
                        <span class="font-semibold" x-text="projectCode"></span>
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('The project goes back to its current assignee so it can be updated and completed again. This is recorded in the assignment history.') }}
                    </p>

                    <div class="mt-4">
                        <x-input-label for="reopen_note" :value="__('Reason (optional)')" />
                        <x-textarea-input id="reopen_note" name="note" rows="3" maxlength="1000" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('note')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-2 bg-gray-50 px-6 py-4 dark:bg-gray-900/40 sm:flex-row sm:justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800">
                    <x-icon name="arrow-uturn-left" class="h-4 w-4" />
                    {{ __('Revert to In Progress') }}
                </button>
            </div>
        </form>
    </x-modal>
</div>
