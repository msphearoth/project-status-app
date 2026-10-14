<?php

namespace App\Policies;

use App\Enums\ProjectStatus;
use App\Enums\UserPermission;
use App\Models\Project;
use App\Models\User;

/**
 * Access to project features is granted per user through UserPermission
 * (admins hold every permission). Independently of those permissions, users
 * can always see and work on (edit, reassign, complete) the projects assigned
 * to them, and edit pending projects they created.
 */
class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAccessTo(UserPermission::ViewProjects);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->hasAccessTo(UserPermission::ViewProjects)
            || $this->isAssignedTo($user, $project)
            || (int) $project->created_by === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAccessTo(UserPermission::CreateProjects);
    }

    /**
     * Determine whether the user can create projects from a spreadsheet.
     */
    public function import(User $user): bool
    {
        return $user->hasAccessTo(UserPermission::ImportProjects);
    }

    /**
     * Determine whether the user can download the project list.
     */
    public function export(User $user): bool
    {
        return $user->hasAccessTo(UserPermission::ExportProjects);
    }

    /**
     * Determine whether the user can update the model's detail information.
     */
    public function update(User $user, Project $project): bool
    {
        if ($project->status === ProjectStatus::Completed) {
            return false;
        }

        return $user->hasAccessTo(UserPermission::ManageAllProjects)
            || $this->isAssignedTo($user, $project)
            || ($project->status === ProjectStatus::Pending && (int) $project->created_by === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->hasAccessTo(UserPermission::DeleteProjects);
    }

    /**
     * Determine whether the user can assign or reassign the project to another user.
     */
    public function assign(User $user, Project $project): bool
    {
        if ($project->status === ProjectStatus::Completed) {
            return false;
        }

        return $user->hasAccessTo(UserPermission::ManageAllProjects)
            || ($project->status === ProjectStatus::Pending && $user->hasAccessTo(UserPermission::AssignProjects))
            || $this->isAssignedTo($user, $project);
    }

    /**
     * Determine whether the user can remove the current assignee and return the project to pending.
     */
    public function unassign(User $user, Project $project): bool
    {
        return $user->hasAccessTo(UserPermission::UnassignProjects)
            && $project->status === ProjectStatus::InProgress
            && $project->assignee_id !== null;
    }

    /**
     * Determine whether the user can mark the project as completed.
     */
    public function complete(User $user, Project $project): bool
    {
        return $project->status === ProjectStatus::InProgress
            && ($user->hasAccessTo(UserPermission::ManageAllProjects) || $this->isAssignedTo($user, $project));
    }

    /**
     * Determine whether the user can revert a completed project back to in progress.
     */
    public function reopen(User $user, Project $project): bool
    {
        return $project->status === ProjectStatus::Completed
            && $user->hasAccessTo(UserPermission::ReopenProjects);
    }

    /**
     * Determine whether the project is currently assigned to the given user.
     */
    private function isAssignedTo(User $user, Project $project): bool
    {
        return $project->assignee_id !== null && (int) $project->assignee_id === $user->id;
    }
}
