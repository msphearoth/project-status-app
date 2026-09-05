<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Project::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_code' => ['required', 'string', 'max:255', 'unique:projects,project_code'],
            'work_code' => ['required', 'string', 'max:255'],
            'on_road' => ['required', 'string', 'max:255'],
            'start_road' => ['required', 'string', 'max:255'],
            'end_road' => ['required', 'string', 'max:255'],
            'pipe_type' => ['required', 'string', 'max:255'],
            'pipe_diameter' => ['required', 'numeric', 'min:0'],
            'pipe_length' => ['required', 'numeric', 'min:0'],
            'received_date' => ['required', 'date'],
        ];
    }
}
