<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('project'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_code' => [
                'required', 'string', 'max:255',
                Rule::unique('projects', 'project_code')->ignore($this->route('project')),
            ],
            'year' => ['required', 'integer', 'digits:4', 'min:2000', 'max:'.(now()->year + 10)],
            'work_code' => ['required', 'string', 'max:255'],
            'on_road' => ['required', 'string', 'max:255'],
            'start_road' => ['required', 'string', 'max:255'],
            'end_road' => ['required', 'string', 'max:255'],
            'pipe_type' => ['required', 'string', 'max:255'],
            'pipe_diameter' => ['required', 'numeric', 'min:0'],
            'pipe_length' => ['required', 'numeric', 'min:0'],
            'received_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ];
    }

    /**
     * Get custom messages for validator errors, showing dates as DD-MMM-YY.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'received_date.before_or_equal' => __('validation.before_or_equal', ['date' => today()->format('d-M-y')]),
        ];
    }
}
