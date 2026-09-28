<?php

namespace App\Policies;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;

/**
 * Admins can manage every project. Standard users can view, export, create and
 * assign pending projects, and work on (edit, reassign, complete) the projects
 * assigned to them. Deleting and unassigning are admin-only.
 */
class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model's detail information.
     */
    public function update(User $user, Project $project): bool
    {
        if ($project->status === ProjectStatus::Completed) {
            return false;
        }

        return $user->isAdmin()
            || $this->isAssignedTo($user, $project)
            || ($project->status === ProjectStatus::Pending && (int) $project->created_by === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can assign or reassign the project to another user.
     */
    public function assign(User $user, Project $project): bool
    {
        if ($project->status === ProjectStatus::Completed) {
            return false;
        }

        return $user->isAdmin()
            || $project->status === ProjectStatus::Pending
            || $this->isAssignedTo($user, $project);
    }

    /**
     * Determine whether the user can remove the current assignee and return the project to pending.
     */
    public function unassign(User $user, Project $project): bool
    {
        return $user->isAdmin()
            && $project->status === ProjectStatus::InProgress
            && $project->assignee_id !== null;
    }

    /**
     * Determine whether the user can mark the project as completed.
     */
    public function complete(User $user, Project $project): bool
    {
        return $project->status === ProjectStatus::InProgress
            && ($user->isAdmin() || $this->isAssignedTo($user, $project));
    }

    /**
     * Determine whether the project is currently assigned to the given user.
     */
    private function isAssignedTo(User $user, Project $project): bool
    {
        return $project->assignee_id !== null && (int) $project->assignee_id === $user->id;
    }
}
