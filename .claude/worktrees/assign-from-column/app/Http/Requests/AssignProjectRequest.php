<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('assign', $this->route('project'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'assignee_id' => [
                'required',
                Rule::exists('users', 'id'),
                Rule::notIn([$this->route('project')->assignee_id]),
            ],
            'assigned_from' => ['nullable', Rule::exists('users', 'id'), 'different:assignee_id'],
            'note' => ['nullable', 'string', 'max:1000'],
            'assigned_on' => [
                'nullable',
                'date_format:Y-m-d',
                'before_or_equal:today',
                'after_or_equal:'.$this->route('project')->received_date->format('Y-m-d'),
            ],
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
            'assigned_on.before_or_equal' => __('validation.before_or_equal', ['date' => today()->format('d-M-y')]),
            'assigned_on.after_or_equal' => __('validation.after_or_equal', ['date' => $this->route('project')->received_date->format('d-M-y')]),
        ];
    }
}
