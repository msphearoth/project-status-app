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

    public function test_dashboard_gives_the_same_assignee_a_separate_row_per_year(): void
    {
        $viewer = User::factory()->create();
        $assignee = User::factory()->create();

        $thisYear = Project::factory()->inProgress()->create([
            'created_by' => $viewer->id,
            'assignee_id' => $assignee->id,
            'year' => now()->year,
        ]);
        $this->assignProject($thisYear, $viewer, $assignee, 1);

        $lastYear = Project::factory()->inProgress()->create([
            'created_by' => $viewer->id,
            'assignee_id' => $assignee->id,
            'year' => now()->year - 1,
        ]);
        $this->assignProject($lastYear, $viewer, $assignee, 1);

        $response = $this->actingAs($viewer)->get(route('dashboard'));

        $response->assertViewHas('rows', function ($rows) use ($assignee) {
            $matching = $rows->filter(fn (array $row) => $row['assignee']?->id === $assignee->id);

            return $matching->count() === 2
                && $matching->pluck('year')->sort()->values()->all() === [now()->year - 1, now()->year];
        });
    }

    public function test_dashboard_year_rowspan_covers_every_assignee_row_in_that_year(): void
    {
        $viewer = User::factory()->create();
        $first = User::factory()->create(['name' => 'Aaa Assignee']);
        $second = User::factory()->create(['name' => 'Bbb Assignee']);

        $firstProject = Project::factory()->inProgress()->create([
            'created_by' => $viewer->id,
            'assignee_id' => $first->id,
            'year' => now()->year,
        ]);
        $this->assignProject($firstProject, $viewer, $first, 1);

        $secondProject = Project::factory()->inProgress()->create([
            'created_by' => $viewer->id,
            'assignee_id' => $second->id,
            'year' => now()->year,
        ]);
        $this->assignProject($secondProject, $viewer, $second, 1);

        $response = $this->actingAs($viewer)->get(route('dashboard'));

        $response->assertViewHas('rows', function ($rows) {
            $rows = $rows->values();

            return $rows->count() === 2
                && $rows[0]['yearRowspan'] === 2
                && $rows[1]['yearRowspan'] === null;
        });
    }
}
