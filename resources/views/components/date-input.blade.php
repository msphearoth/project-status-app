@props(['id', 'name', 'value' => null, 'min' => null, 'max' => null, 'required' => false, 'width' => 'sm:w-72', 'shortcuts' => false])

{{--
    A date field shown as DD-MMM-YY (e.g. 26-Sep-26) that accepts typed input
    (DD-MMM-YY, DD-MMM-YYYY, DD-MM-YY, DD/MM/YYYY or YYYY-MM-DD) or a pick from
    the native calendar. Always submits YYYY-MM-DD through a hidden input.
--}}
<div x-data="{
        isoValue: @js($value ?? ''),
        displayValue: '',
        invalid: false,
        months: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        init() {
            this.displayValue = this.toDisplay(this.isoValue);
            this.$el.closest('form')?.addEventListener('submit', (event) => {
                this.normalize();
                if (this.invalid) {
                    event.preventDefault();
                    this.$refs.text.focus();
                }
            });
        },
        toDisplay(iso) {
            const match = iso.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            return match ? `${match[3]}-${this.months[Number(match[2]) - 1]}-${match[1].slice(2)}` : iso;
        },
        normalize() {
            const text = this.displayValue.trim();
            if (text === '') {
                this.invalid = false;
                this.isoValue = '';
                this.$refs.hidden.value = '';
                return;
            }

            let year, month, day, match;
            if ((match = text.match(/^(\d{4})[-\/.](\d{1,2})[-\/.](\d{1,2})$/))) {
                [, year, month, day] = match;
            } else if ((match = text.match(/^(\d{1,2})[-\/. ]([a-z]{3,})[-\/. ](\d{2}|\d{4})$/i))) {
                [, day, month, year] = match;
                month = this.months.findIndex((name) => name.toLowerCase() === month.slice(0, 3).toLowerCase()) + 1;
                if (month === 0) {
                    this.invalid = true;
                    return;
                }
                if (year.length === 2) {
                    year = '20' + year;
                }
            } else if ((match = text.match(/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{2}|\d{4})$/))) {
                [, day, month, year] = match;
                if (year.length === 2) {
                    year = '20' + year;
                }
            } else {
                this.invalid = true;
                return;
            }

            const date = new Date(Number(year), Number(month) - 1, Number(day));
            if (date.getFullYear() !== Number(year) || date.getMonth() !== Number(month) - 1 || date.getDate() !== Number(day)) {
                this.invalid = true;
                return;
            }

            this.invalid = false;
            this.isoValue = [year, String(month).padStart(2, '0'), String(day).padStart(2, '0')].join('-');
            this.displayValue = this.toDisplay(this.isoValue);
            this.$refs.hidden.value = this.isoValue;
            this.$refs.text.value = this.displayValue;
        },
        daysAgo(days) {
            const date = new Date();
            date.setDate(date.getDate() - days);
            return [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
        },
        setDaysAgo(days) {
            this.isoValue = this.daysAgo(days);
            this.displayValue = this.toDisplay(this.isoValue);
            this.invalid = false;
        },
        openPicker() {
            this.normalize();
            this.$refs.picker.value = this.invalid ? '' : this.isoValue;
            this.$refs.picker.showPicker ? this.$refs.picker.showPicker() : this.$refs.picker.click();
        },
    }" class="mt-1">
    <input type="hidden" name="{{ $name }}" x-ref="hidden" x-bind:value="isoValue" value="{{ $value }}">

    <div class="relative flex w-full {{ $width }}">
        <input id="{{ $id }}" type="text" autocomplete="off"
            x-ref="text" placeholder="DD-MMM-YY" x-model="displayValue" x-on:blur="normalize()" x-on:keydown.enter="normalize()"
            @required($required)
            {{ $attributes->merge(['class' => 'block w-full rounded-s-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm']) }}
            x-bind:class="invalid && 'border-red-500 dark:border-red-500'">

        <button type="button" x-on:click="openPicker()" title="{{ __('Pick a date') }}" aria-label="{{ __('Pick a date') }}"
            class="inline-flex items-center px-3 -ms-px rounded-e-md border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
            </svg>
        </button>

        <input type="date" x-ref="picker" tabindex="-1" aria-hidden="true"
            @if ($min) min="{{ $min }}" @endif
            @if ($max) max="{{ $max }}" @endif
            x-on:change="isoValue = $event.target.value; displayValue = toDisplay(isoValue); invalid = false"
            class="absolute bottom-0 end-0 h-0 w-0 opacity-0 pointer-events-none">
    </div>

    @if ($shortcuts)
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach ([0 => __('Today'), 1 => __('Yesterday')] as $days => $label)
                <button type="button" x-on:click="setDaysAgo({{ $days }})"
                    class="px-2.5 py-1 text-xs rounded-md border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                    x-bind:class="isoValue === daysAgo({{ $days }}) && 'bg-indigo-50 border-indigo-400 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    @endif

    <p x-show="invalid" style="display: none;" class="mt-2 text-sm text-red-600 dark:text-red-400">
        {{ __('Please enter a valid date in DD-MMM-YY format, e.g. 26-Sep-26.') }}
    </p>
</div>
