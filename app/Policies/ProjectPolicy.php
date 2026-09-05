<?php

namespace App\Policies;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;

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
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model's detail information.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->isAdmin() && $project->status !== ProjectStatus::Completed;
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

        if ($project->status === ProjectStatus::Pending) {
            return $user->isAdmin();
        }

        return $user->isAdmin() || $user->id === $project->assignee_id;
    }

    /**
     * Determine whether the user can mark the project as completed.
     */
    public function complete(User $user, Project $project): bool
    {
        return $project->status === ProjectStatus::InProgress
            && ($user->isAdmin() || $user->id === $project->assignee_id);
    }
}
