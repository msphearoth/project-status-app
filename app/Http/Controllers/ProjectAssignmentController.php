<?php

namespace App\Http\Controllers;

use App\Enums\ProjectAssignmentAction;
use App\Enums\ProjectStatus;
use App\Http\Requests\AssignProjectRequest;
use App\Http\Requests\CompleteProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProjectAssignmentController extends Controller
{
    /**
     * Assign or reassign the project to another user.
     */
    public function assign(AssignProjectRequest $request, Project $project): RedirectResponse
    {
        $action = $project->status === ProjectStatus::Pending
            ? ProjectAssignmentAction::Assigned
            : ProjectAssignmentAction::Reassigned;

        DB::transaction(function () use ($request, $project, $action) {
            $project->update([
                'assignee_id' => $request->validated('assignee_id'),
                'status' => ProjectStatus::InProgress,
            ]);

            $project->assignmentLogs()->create([
                'action' => $action,
                'assigned_by' => $request->user()->id,
                'assigned_to' => $request->validated('assignee_id'),
                'note' => $request->validated('note'),
            ]);
        });

        return redirect()->route('projects.show', $project)
            ->with('status', __('Project assigned successfully.'));
    }

    /**
     * Mark the project as completed.
     */
    public function complete(CompleteProjectRequest $request, Project $project): RedirectResponse
    {
        DB::transaction(function () use ($request, $project) {
            $project->update([
                'status' => ProjectStatus::Completed,
                'project_amount' => $request->validated('project_amount'),
                'request_number' => $request->validated('request_number'),
                'completed_at' => now(),
            ]);

            $project->assignmentLogs()->create([
                'action' => ProjectAssignmentAction::Completed,
                'assigned_by' => $request->user()->id,
                'assigned_to' => $project->assignee_id,
            ]);
        });

        return redirect()->route('projects.show', $project)
            ->with('status', __('Project marked as completed.'));
    }
}
