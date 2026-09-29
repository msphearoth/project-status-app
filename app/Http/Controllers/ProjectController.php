<?php

namespace App\Http\Controllers;

use App\Exports\ProjectsExport;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Project::class);

        $projects = $this->filteredQuery($request)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('projects.index', [
            'projects' => $projects,
            'assignees' => User::orderBy('name')->get(),
        ]);
    }

    /**
     * Download the filtered project list (same filters as the index) as an
     * Excel spreadsheet, including every project field and the current
     * assignee's name and latest assignment date.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('viewAny', Project::class);

        $projects = $this->filteredQuery($request)->latest()->get();

        $filename = 'projects-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new ProjectsExport($projects), $filename);
    }

    /**
     * @return Builder<Project>
     */
    private function filteredQuery(Request $request): Builder
    {
        $request->validate([
            'deca_no' => ['nullable', 'string', 'regex:/^[A-Za-z0-9-]+$/', 'max:50'],
        ]);

        return Project::query()
            ->with(['assignee', 'creator', 'latestAssignmentLog'])
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->when($request->integer('assignee_id'), fn ($query, $assigneeId) => $query->where('assignee_id', $assigneeId))
            ->when($request->string('deca_no')->trim()->toString(), fn ($query, $decaNo) => $query->where('deca_no', 'like', "%{$decaNo}%"))
            ->when($request->string('search')->toString(), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('project_code', 'like', "%{$search}%")
                        ->orWhere('work_code', 'like', "%{$search}%")
                        ->orWhere('deca_no', 'like', "%{$search}%")
                        ->orWhere('on_road', 'like', "%{$search}%");
                });
            });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Project::class);

        return view('projects.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = Project::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('projects.show', $project)
            ->with('status', __('Project created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): View
    {
        $this->authorize('view', $project);

        $project->load(['assignee', 'creator', 'assignmentLogs.assignedBy', 'assignmentLogs.assignedFrom', 'assignmentLogs.assignedTo']);

        return view('projects.show', [
            'project' => $project,
            'users' => User::orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.edit', [
            'project' => $project,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return redirect()->route('projects.show', $project)
            ->with('status', __('Project updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('status', __('Project deleted successfully.'));
    }
}
