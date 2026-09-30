<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Tells a user that a project has been assigned to them. Stored in the
 * database and shown in the notification bell in the navigation.
 */
class ProjectAssigned extends Notification
{
    use Queueable;

    public function __construct(
        public Project $project,
        public User $assignedBy,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{project_id: int, project_code: string, assigned_by_name: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'project_id' => $this->project->id,
            'project_code' => $this->project->project_code,
            'assigned_by_name' => $this->assignedBy->name,
        ];
    }
}
