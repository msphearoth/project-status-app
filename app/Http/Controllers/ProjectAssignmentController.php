<?php

namespace App\Http\Controllers;

use App\Enums\ProjectAssignmentAction;
use App\Enums\ProjectStatus;
use App\Http\Requests\AssignProjectRequest;
use App\Http\Requests\BulkAssignProjectsRequest;
use App\Http\Requests\BulkCompleteProjectsRequest;
use App\Http\Requests\CompleteProjectRequest;
use App\Http\Requests\ReopenProjectRequest;
use App\Models\Project;
use App\Models\User;
use App\Notifications\ProjectAssigned;
use Illuminate\Http\JsonResponse;
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
            assignee: User::findOrFail($request->validated('assignee_id')),
            assignedBy: $request->user(),
            assignedFromId: $request->validated('assigned_from'),
            note: $request->validated('note'),
            decaNo: $request->validated('deca_no'),
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
        $assignee = User::findOrFail($request->validated('assignee_id'));

        DB::transaction(function () use ($request, $projects, $assignee) {
            foreach ($projects as $project) {
                $this->assignProject(
                    $project,
                    assignee: $assignee,
                    assignedBy: $request->user(),
                    note: $request->validated('note'),
                    decaNo: $request->validated('deca_no'),
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
     * Revert a completed project back to in progress with its current assignee,
     * recording the optional reason in the assignment history. A project whose
     * assignee no longer exists goes back to pending instead. The request number
     * is kept so it can be reused when the project is completed again.
     */
    public function reopen(ReopenProjectRequest $request, Project $project): RedirectResponse
    {
        DB::transaction(function () use ($request, $project) {
            $project->update([
                'status' => $project->assignee_id ? ProjectStatus::InProgress : ProjectStatus::Pending,
                'completed_at' => null,
            ]);

            $project->assignmentLogs()->create([
                'action' => ProjectAssignmentAction::Reopened,
                'assigned_by' => $request->user()->id,
                'assigned_to' => $project->assignee_id,
                'note' => $request->validated('note'),
            ]);
        });

        return redirect()->route('projects.show', $project)
            ->with('status', __('Project reopened and set back to :status.', ['status' => $project->status->label()]));
    }

    /**
     * Mark several selected in-progress projects as completed at once, saving
     * the same request number on each. Called from the project list via AJAX;
     * the message is also flashed so it shows once the list reloads.
     */
    public function bulkComplete(BulkCompleteProjectsRequest $request): JsonResponse
    {
        $projects = $request->projects();

        DB::transaction(function () use ($request, $projects) {
            foreach ($projects as $project) {
                $project->update([
                    'status' => ProjectStatus::Completed,
                    'request_number' => $request->validated('request_number'),
                    'completed_at' => now(),
                ]);

                $project->assignmentLogs()->create([
                    'action' => ProjectAssignmentAction::Completed,
                    'assigned_by' => $request->user()->id,
                    'assigned_to' => $project->assignee_id,
                ]);
            }
        });

        $message = trans_choice(':count project marked as completed.|:count projects marked as completed.', $projects->count());

        session()->flash('status', $message);

        return response()->json([
            'message' => $message,
            'completed_ids' => $projects->pluck('id'),
        ]);
    }

    /**
     * Hand the project to the assignee and record it in the assignment history.
     * Pending projects are logged as assigned; others as reassigned from the
     * given user, falling back to the current assignee. The assignee is
     * notified unless they assigned the project to themselves. A given Deca No.
     * also replaces the project's own, while a blank one leaves it unchanged.
     */
    private function assignProject(
        Project $project,
        User $assignee,
        User $assignedBy,
        int|string|null $assignedFromId = null,
        ?string $note = null,
        ?string $decaNo = null,
        ?string $assignedOn = null,
    ): void {
        $action = $project->status === ProjectStatus::Pending
            ? ProjectAssignmentAction::Assigned
            : ProjectAssignmentAction::Reassigned;

        $assignedFrom = $action === ProjectAssignmentAction::Reassigned
            ? ($assignedFromId ?? $project->assignee_id)
            : null;

        $project->update([
            'assignee_id' => $assignee->id,
            'status' => ProjectStatus::InProgress,
            'deca_no' => $decaNo ?? $project->deca_no,
        ]);

        $project->assignmentLogs()->create([
            'action' => $action,
            'assigned_by' => $assignedBy->id,
            'assigned_from' => $assignedFrom,
            'assigned_to' => $assignee->id,
            'note' => $note,
            'deca_no' => $decaNo,
            'assigned_on' => $assignedOn ?? today(),
        ]);

        if ($assignee->isNot($assignedBy)) {
            $assignee->notify(new ProjectAssigned($project, $assignedBy));
        }
    }
}
