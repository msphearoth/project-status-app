<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BulkDeleteProjectsRequest extends FormRequest
{
    /**
     * Keep errors apart from the project list filters and the bulk assign form.
     *
     * @var string
     */
    protected $errorBag = 'bulkDelete';

    /**
     * @var Collection<int, Project>|null
     */
    private ?Collection $selectedProjects = null;

    /**
     * Determine if the user is authorized to make this request. Per-project
     * permissions are checked after validation so the errors can name the
     * projects that cannot be deleted.
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

                $notAllowed = $this->projects()->reject(fn (Project $project) => $this->user()->can('delete', $project));

                if ($notAllowed->isNotEmpty()) {
                    $validator->errors()->add('project_ids', __('You are not allowed to delete: :codes.', [
                        'codes' => $notAllowed->pluck('project_code')->join(', '),
                    ]));
                }
            },
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
