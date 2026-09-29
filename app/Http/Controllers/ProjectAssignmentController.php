<?php

namespace App\Http\Controllers;

use App\Enums\ProjectAssignmentAction;
use App\Enums\ProjectStatus;
use App\Http\Requests\AssignProjectRequest;
use App\Http\Requests\BulkAssignProjectsRequest;
use App\Http\Requests\CompleteProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectAssignmentController extends Controller
{
    /**
     * Assign or reassign the project to another user.
     */
    public function assign(AssignProjectRequest $request, Project $project): RedirectResponse
    {
        DB::transaction(fn () => $this->assignProject(
            $project,
            assigneeId: (int) $request->validated('assignee_id'),
            assignedById: $request->user()->id,
            assignedFromId: $request->validated('assigned_from'),
            note: $request->validated('note'),
            assignedOn: $request->validated('assigned_on'),
        ));

        return redirect()->route('projects.show', $project)
            ->with('status', __('Project assigned successfully.'));
    }

    /**
     * Assign or reassign several selected projects to one user at once.
     */
    public function bulkAssign(BulkAssignProjectsRequest $request): RedirectResponse
    {
        $projects = $request->projects();

        DB::transaction(function () use ($request, $projects) {
            foreach ($projects as $project) {
                $this->assignProject(
                    $project,
                    assigneeId: (int) $request->validated('assignee_id'),
                    assignedById: $request->user()->id,
                    note: $request->validated('note'),
                    assignedOn: $request->validated('assigned_on'),
                );
            }
        });

        return redirect()->back()
            ->with('status', trans_choice(':count project assigned successfully.|:count projects assigned successfully.', $projects->count()));
    }

    /**
     * Remove the current assignee and return the project to pending.
     */
    public function unassign(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('unassign', $project);

        DB::transaction(function () use ($request, $project) {
            $project->assignmentLogs()->create([
                'action' => ProjectAssignmentAction::Unassigned,
                'assigned_by' => $request->user()->id,
                'assigned_from' => $project->assignee_id,
            ]);

            $project->update([
                'assignee_id' => null,
                'status' => ProjectStatus::Pending,
            ]);
        });

        return redirect()->route('projects.show', $project)
            ->with('status', __('Project assignment removed.'));
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

    /**
     * Hand the project to the assignee and record it in the assignment history.
     * Pending projects are logged as assigned; others as reassigned from the
     * given user, falling back to the current assignee.
     */
    private function assignProject(
        Project $project,
        int $assigneeId,
        int $assignedById,
        int|string|null $assignedFromId = null,
        ?string $note = null,
        ?string $assignedOn = null,
    ): void {
        $action = $project->status === ProjectStatus::Pending
            ? ProjectAssignmentAction::Assigned
            : ProjectAssignmentAction::Reassigned;

        $assignedFrom = $action === ProjectAssignmentAction::Reassigned
            ? ($assignedFromId ?? $project->assignee_id)
            : null;

        $project->update([
            'assignee_id' => $assigneeId,
            'status' => ProjectStatus::InProgress,
        ]);

        $project->assignmentLogs()->create([
            'action' => $action,
            'assigned_by' => $assignedById,
            'assigned_from' => $assignedFrom,
            'assigned_to' => $assigneeId,
            'note' => $note,
            'assigned_on' => $assignedOn ?? today(),
        ]);
    }
}
