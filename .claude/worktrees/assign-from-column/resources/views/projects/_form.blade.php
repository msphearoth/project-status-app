@php
    $project = $project ?? null;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="project_code" :value="__('Project Code')" />
        <x-text-input id="project_code" name="project_code" type="text" class="mt-1 block w-full"
            :value="old('project_code', $project?->project_code)" required autofocus />
        <x-input-error :messages="$errors->get('project_code')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="year" :value="__('Year')" />
        <x-text-input id="year" name="year" type="number" step="1" min="2000" class="mt-1 block w-full"
            :value="old('year', $project?->year ?? now()->year)" required />
        <x-input-error :messages="$errors->get('year')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="work_code" :value="__('Work Code')" />
        <x-text-input id="work_code" name="work_code" type="text" class="mt-1 block w-full"
            :value="old('work_code', $project?->work_code)" required />
        <x-input-error :messages="$errors->get('work_code')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="on_road" :value="__('On Road')" />
        <x-text-input id="on_road" name="on_road" type="text" class="mt-1 block w-full"
            :value="old('on_road', $project?->on_road)" required />
        <x-input-error :messages="$errors->get('on_road')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="pipe_type" :value="__('Pipe Type')" />
        <x-text-input id="pipe_type" name="pipe_type" type="text" class="mt-1 block w-full"
            :value="old('pipe_type', $project?->pipe_type)" required />
        <x-input-error :messages="$errors->get('pipe_type')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="start_road" :value="__('Start Road')" />
        <x-text-input id="start_road" name="start_road" type="text" class="mt-1 block w-full"
            :value="old('start_road', $project?->start_road)" required />
        <x-input-error :messages="$errors->get('start_road')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="end_road" :value="__('End Road')" />
        <x-text-input id="end_road" name="end_road" type="text" class="mt-1 block w-full"
            :value="old('end_road', $project?->end_road)" required />
        <x-input-error :messages="$errors->get('end_road')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="pipe_diameter" :value="__('Pipe Diameter')" />
        <x-text-input id="pipe_diameter" name="pipe_diameter" type="number" step="0.01" min="0" class="mt-1 block w-full"
            :value="old('pipe_diameter', $project?->pipe_diameter)" required />
        <x-input-error :messages="$errors->get('pipe_diameter')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="pipe_length" :value="__('Pipe Length')" />
        <x-text-input id="pipe_length" name="pipe_length" type="number" step="0.01" min="0" class="mt-1 block w-full"
            :value="old('pipe_length', $project?->pipe_length)" required />
        <x-input-error :messages="$errors->get('pipe_length')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="received_date" :value="__('Received Date')" />
        <x-date-input id="received_date" name="received_date" required shortcuts width="w-full"
            :value="old('received_date', $project?->received_date?->format('Y-m-d') ?? now()->format('Y-m-d'))"
            :max="now()->format('Y-m-d')" />
        <x-input-error :messages="$errors->get('received_date')" class="mt-2" />
    </div>
</div>
