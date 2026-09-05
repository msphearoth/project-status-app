<?php

namespace Database\Seeders;

use App\Enums\ProjectAssignmentAction;
use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $staff = User::where('role', UserRole::User)->get();

        Project::factory()
            ->count(5)
            ->create(['created_by' => $admin->id]);

        Project::factory()
            ->inProgress()
            ->count(5)
            ->create(['created_by' => $admin->id])
            ->each(function (Project $project) use ($admin, $staff) {
                $assignee = $staff->random();
                $project->update(['assignee_id' => $assignee->id]);

                $project->assignmentLogs()->create([
                    'action' => ProjectAssignmentAction::Assigned,
                    'assigned_by' => $admin->id,
                    'assigned_to' => $assignee->id,
                ]);
            });

        Project::factory()
            ->completed()
            ->count(5)
            ->create(['created_by' => $admin->id])
            ->each(function (Project $project) use ($admin, $staff) {
                $assignee = $staff->random();
                $project->update(['assignee_id' => $assignee->id]);

                $project->assignmentLogs()->create([
                    'action' => ProjectAssignmentAction::Assigned,
                    'assigned_by' => $admin->id,
                    'assigned_to' => $assignee->id,
                ]);

                $project->assignmentLogs()->create([
                    'action' => ProjectAssignmentAction::Completed,
                    'assigned_by' => $assignee->id,
                    'assigned_to' => $assignee->id,
                ]);
            });
    }
}
