<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BulkAssignProjectsRequest extends FormRequest
{
    /**
     * Keep errors apart from the project list filters so the bulk assign modal can reopen with them.
     *
     * @var string
     */
    protected $errorBag = 'bulkAssign';

    /**
     * @var Collection<int, Project>|null
     */
    private ?Collection $selectedProjects = null;

    /**
     * Determine if the user is authorized to make this request. Per-project
     * permissions are checked after validation so the errors can name the
     * projects that cannot be assigned.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_ids' => ['required', 'array', 'min:1', 'max:100'],
            'project_ids.*' => ['integer', 'distinct', Rule::exists('projects', 'id')],
            'assignee_id' => ['required', Rule::exists('users', 'id')],
            'note' => ['nullable', 'string', 'max:1000'],
            'assigned_on' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $projects = $this->projects();
                $assigneeId = (int) $this->input('assignee_id');
                $assignedOn = Carbon::parse($this->input('assigned_on') ?? today());

                $notAllowed = $projects->reject(fn (Project $project) => $this->user()->can('assign', $project));
                $alreadyAssigned = $projects->filter(fn (Project $project) => (int) $project->assignee_id === $assigneeId);
                $receivedLater = $projects->filter(fn (Project $project) => $project->received_date->gt($assignedOn));

                if ($notAllowed->isNotEmpty()) {
                    $validator->errors()->add('project_ids', __('You are not allowed to assign: :codes.', [
                        'codes' => $notAllowed->pluck('project_code')->join(', '),
                    ]));
                }

                if ($alreadyAssigned->isNotEmpty()) {
                    $validator->errors()->add('project_ids', __('Already assigned to the selected user: :codes.', [
                        'codes' => $alreadyAssigned->pluck('project_code')->join(', '),
                    ]));
                }

                if ($receivedLater->isNotEmpty()) {
                    $validator->errors()->add('assigned_on', __('The assigned date cannot be before the received date of: :codes.', [
                        'codes' => $receivedLater->pluck('project_code')->join(', '),
                    ]));
                }
            },
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
        ];
    }

    /**
     * The selected projects, loaded once.
     *
     * @return Collection<int, Project>
     */
    public function projects(): Collection
    {
        return $this->selectedProjects ??= Project::query()
            ->whereIn('id', $this->input('project_ids', []))
            ->orderBy('project_code')
            ->get();
    }
}
