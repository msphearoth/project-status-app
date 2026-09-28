<?php

namespace Tests\Feature;

use App\Enums\ProjectAssignmentAction;
use App\Enums\ProjectStatus;
use App\Exports\ProjectsExport;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ProjectsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_export_includes_all_fields_and_the_latest_assignee(): void
    {
        Excel::fake();

        $creator = User::factory()->create(['name' => 'Creator Person']);
        $assignee = User::factory()->create(['name' => 'Assignee Person']);

        $project = Project::factory()->inProgress()->create([
            'project_code' => 'PRJ-EXPORT1',
            'year' => 2025,
            'work_code' => 'WRK-EXPORT1',
            'created_by' => $creator->id,
            'assignee_id' => $assignee->id,
        ]);

        $this->travelTo(now()->subDays(3));
        $project->assignmentLogs()->create([
            'action' => ProjectAssignmentAction::Assigned,
            'assigned_by' => $creator->id,
            'assigned_to' => $assignee->id,
        ]);
        $this->travelBack();

        $response = $this->actingAs($creator)->get(route('projects.export'));

        $response->assertOk();

        $filename = 'projects-'.now()->format('Y-m-d').'.xlsx';

        Excel::assertDownloaded($filename, function (ProjectsExport $export) use ($assignee) {
            $headings = $export->headings();
            $row = $export->array()[0];

            return $headings[0] === 'Project Code'
                && $headings[11] === 'Assign Project From'
                && $headings[12] === 'Assignee'
                && $row[0] === 'PRJ-EXPORT1'
                && $row[1] === 2025
                && $row[2] === 'WRK-EXPORT1'
                && $row[10] === ProjectStatus::InProgress->label()
                && $row[11] === ''
                && $row[12] === $assignee->name
                && $row[13] === now()->subDays(3)->format('Y-m-d');
        });
    }

    public function test_projects_export_respects_the_status_filter(): void
    {
        Excel::fake();

        $user = User::factory()->create();

        Project::factory()->create(['created_by' => $user->id, 'project_code' => 'PRJ-PENDING']);
        Project::factory()->completed()->create(['created_by' => $user->id, 'project_code' => 'PRJ-COMPLETED']);

        $response = $this->actingAs($user)->get(route('projects.export', ['status' => ProjectStatus::Completed->value]));

        $response->assertOk();

        $filename = 'projects-'.now()->format('Y-m-d').'.xlsx';

        Excel::assertDownloaded($filename, function (ProjectsExport $export) {
            $rows = $export->array();

            return count($rows) === 1 && $rows[0][0] === 'PRJ-COMPLETED';
        });
    }

    public function test_projects_export_shows_unassigned_for_pending_projects(): void
    {
        Excel::fake();

        $user = User::factory()->create();
        Project::factory()->create(['created_by' => $user->id, 'project_code' => 'PRJ-NOASSIGNEE']);

        $this->actingAs($user)->get(route('projects.export'))->assertOk();

        $filename = 'projects-'.now()->format('Y-m-d').'.xlsx';

        Excel::assertDownloaded($filename, function (ProjectsExport $export) {
            $row = $export->array()[0];

            return $row[11] === '' && $row[12] === 'Unassigned' && $row[13] === '';
        });
    }
}
