<?php

namespace Tests\Feature;

use App\Enums\ProjectAssignmentAction;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function assignProject(Project $project, User $by, User $to, int $daysAgo): void
    {
        $this->travelTo(now()->subDays($daysAgo));

        $project->assignmentLogs()->create([
            'action' => ProjectAssignmentAction::Assigned,
            'assigned_by' => $by->id,
            'assigned_to' => $to->id,
        ]);

        $this->travelBack();
    }

    public function test_dashboard_buckets_in_progress_projects_by_days_since_assignment(): void
    {
        $viewer = User::factory()->create();
        $assignee = User::factory()->create();

        $recent = Project::factory()->inProgress()->create(['created_by' => $viewer->id, 'assignee_id' => $assignee->id]);
        $this->assignProject($recent, $viewer, $assignee, 2);

        $mid = Project::factory()->inProgress()->create(['created_by' => $viewer->id, 'assignee_id' => $assignee->id]);
        $this->assignProject($mid, $viewer, $assignee, 7);

        $old = Project::factory()->inProgress()->create(['created_by' => $viewer->id, 'assignee_id' => $assignee->id]);
        $this->assignProject($old, $viewer, $assignee, 15);

        $response = $this->actingAs($viewer)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('rows', function ($rows) use ($assignee) {
            $row = $rows->firstWhere('assignee.id', $assignee->id);

            return $row
                && $row['under5'] === 1
                && $row['between5and10'] === 1
                && $row['over10'] === 1
                && $row['total'] === 3;
        });
    }

    public function test_dashboard_bucket_boundaries_are_inclusive_on_the_lower_edge(): void
    {
        $viewer = User::factory()->create();
        $assignee = User::factory()->create();

        $exactlyFive = Project::factory()->inProgress()->create(['created_by' => $viewer->id, 'assignee_id' => $assignee->id]);
        $this->assignProject($exactlyFive, $viewer, $assignee, 5);

        $exactlyTen = Project::factory()->inProgress()->create(['created_by' => $viewer->id, 'assignee_id' => $assignee->id]);
        $this->assignProject($exactlyTen, $viewer, $assignee, 10);

        $response = $this->actingAs($viewer)->get(route('dashboard'));

        $response->assertViewHas('rows', function ($rows) use ($assignee) {
            $row = $rows->firstWhere('assignee.id', $assignee->id);

            return $row
                && $row['under5'] === 0
                && $row['between5and10'] === 1
                && $row['over10'] === 1;
        });
    }

    public function test_dashboard_excludes_pending_and_completed_projects(): void
    {
        $viewer = User::factory()->create();
        $assignee = User::factory()->create();

        Project::factory()->create(['created_by' => $viewer->id]);
        Project::factory()->completed()->create(['created_by' => $viewer->id, 'assignee_id' => $assignee->id]);

        $response = $this->actingAs($viewer)->get(route('dashboard'));

        $response->assertViewHas('rows', fn ($rows) => $rows->isEmpty());
    }

    public function test_dashboard_shows_empty_state_with_no_in_progress_projects(): void
    {
        $viewer = User::factory()->create();

        $this->actingAs($viewer)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('No in-progress projects.');
    }
}
