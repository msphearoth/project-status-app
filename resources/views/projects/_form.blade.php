@php
    $project = $project ?? null;
    $sections = [
        'identification' => ['icon' => 'hashtag', 'title' => __('Identification'), 'description' => __('The codes used to identify and look up this project.')],
        'location' => ['icon' => 'map-pin', 'title' => __('Location'), 'description' => __('The road the work is on and where it starts and ends.')],
        'pipe' => ['icon' => 'adjustments', 'title' => __('Pipe Specification'), 'description' => __('Type and size of the pipe to be laid.')],
        'dates' => ['icon' => 'calendar', 'title' => __('Dates'), 'description' => __('When the project request was received.')],
    ];
@endphp

<div class="space-y-6">
    <p class="text-sm text-gray-500 dark:text-gray-400">
        {!! __('Fields marked :mark are required.', ['mark' => '<span class="text-red-500">*</span>']) !!}
    </p>

    @foreach ($sections as $section => $meta)
        <div class="grid grid-cols-1 gap-x-8 gap-y-4 lg:grid-cols-3">
            <div class="flex items-start gap-3 lg:pt-5">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon :name="$meta['icon']" class="h-5 w-5" />
                </span>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $meta['title'] }}</h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ $meta['description'] }}</p>
                </div>
            </div>

            <x-card class="lg:col-span-2">
                <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 sm:p-6">
                    @switch($section)
                        @case('identification')
                            <div>
                                <x-input-label for="project_code" :value="__('Project Code')" required />
                                <x-text-input id="project_code" name="project_code" type="text" class="mt-1 block w-full"
                                    :value="old('project_code', $project?->project_code)" required autofocus />
                                <x-input-error :messages="$errors->get('project_code')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="year" :value="__('Year')" required />
                                <x-text-input id="year" name="year" type="number" step="1" min="2000" class="mt-1 block w-full"
                                    :value="old('year', $project?->year ?? now()->year)" required />
                                <x-input-error :messages="$errors->get('year')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="work_code" :value="__('Work Code')" required />
                                <x-text-input id="work_code" name="work_code" type="text" class="mt-1 block w-full"
                                    :value="old('work_code', $project?->work_code)" required />
                                <x-input-error :messages="$errors->get('work_code')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="deca_no" :value="__('Deca No.')" />
                                <x-text-input id="deca_no" name="deca_no" type="text" maxlength="50" pattern="[A-Za-z0-9\-]*" class="mt-1 block w-full font-mono"
                                    title="{{ __('Letters, numbers and hyphens (-) only') }}" placeholder="{{ __('e.g. DC-1001') }}"
                                    :value="old('deca_no', $project?->deca_no)" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Letters, numbers and hyphens (-) only') }}</p>
                                <x-input-error :messages="$errors->get('deca_no')" class="mt-2" />
                            </div>
                            @break

                        @case('location')
                            <div class="sm:col-span-2">
                                <x-input-label for="on_road" :value="__('On Road')" required />
                                <x-text-input id="on_road" name="on_road" type="text" class="mt-1 block w-full"
                                    :value="old('on_road', $project?->on_road)" required />
                                <x-input-error :messages="$errors->get('on_road')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="start_road" :value="__('Start Road')" required />
                                <x-text-input id="start_road" name="start_road" type="text" class="mt-1 block w-full"
                                    :value="old('start_road', $project?->start_road)" required />
                                <x-input-error :messages="$errors->get('start_road')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="end_road" :value="__('End Road')" required />
                                <x-text-input id="end_road" name="end_road" type="text" class="mt-1 block w-full"
                                    :value="old('end_road', $project?->end_road)" required />
                                <x-input-error :messages="$errors->get('end_road')" class="mt-2" />
                            </div>
                            @break

                        @case('pipe')
                            <div class="sm:col-span-2">
                                <x-input-label for="pipe_type" :value="__('Pipe Type')" required />
                                <x-text-input id="pipe_type" name="pipe_type" type="text" class="mt-1 block w-full"
                                    :value="old('pipe_type', $project?->pipe_type)" required />
                                <x-input-error :messages="$errors->get('pipe_type')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="pipe_diameter" :value="__('Pipe Diameter')" required />
                                <x-text-input id="pipe_diameter" name="pipe_diameter" type="number" step="0.01" min="0" inputmode="decimal" class="mt-1 block w-full"
                                    :value="old('pipe_diameter', $project?->pipe_diameter)" required />
                                <x-input-error :messages="$errors->get('pipe_diameter')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="pipe_length" :value="__('Pipe Length')" required />
                                <x-text-input id="pipe_length" name="pipe_length" type="number" step="0.01" min="0" inputmode="decimal" class="mt-1 block w-full"
                                    :value="old('pipe_length', $project?->pipe_length)" required />
                                <x-input-error :messages="$errors->get('pipe_length')" class="mt-2" />
                            </div>
                            @break

                        @case('dates')
                            <div>
                                <x-input-label for="received_date" :value="__('Received Date')" required />
                                <x-date-input id="received_date" name="received_date" required shortcuts width="w-full"
                                    :value="old('received_date', $project?->received_date?->format('Y-m-d') ?? now()->format('Y-m-d'))"
                                    :max="now()->format('Y-m-d')" />
                                <x-input-error :messages="$errors->get('received_date')" class="mt-2" />
                            </div>
                            @break
                    @endswitch
                </div>
            </x-card>
        </div>
    @endforeach
</div>
