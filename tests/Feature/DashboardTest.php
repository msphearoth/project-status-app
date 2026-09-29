<?php

namespace Tests\Feature;

use App\Enums\ProjectAssignmentAction;
use App\Enums\ProjectStatus;
use App\Exports\DashboardExport;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
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

    public function test_dashboard_can_be_exported_to_excel(): void
    {
        Excel::fake();

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
            'year' => now()->year - 1,
        ]);
        $this->assignProject($secondProject, $viewer, $second, 1);

        $response = $this->actingAs($viewer)->get(route('dashboard.export'));

        $response->assertOk();

        $filename = 'dashboard-'.now()->format('Y-m-d').'.xlsx';

        Excel::assertDownloaded($filename, function (DashboardExport $export) use ($first, $second) {
            $headings = $export->headings();
            $data = $export->array();

            return $headings === ['Year', 'Assignee', 'Less than 5 Days', '5 to 10 Days', '10 Days or More', 'Total']
                && $data[0] === [now()->year, $first->name, 1, 0, 0, 1]
                && $data[1] === [now()->year - 1, $second->name, 1, 0, 0, 1]
                && $data[2] === ['Total', '', 2, 0, 0, 2];
        });
    }

    public function test_dashboard_counts_open_a_drill_down_of_the_matching_projects(): void
    {
        $viewer = User::factory()->create();
        $assignee = User::factory()->create(['name' => 'Drill Assignee']);
        $other = User::factory()->create();

        $recent = Project::factory()->inProgress()->create(['project_code' => 'PRJ-RECENT', 'assignee_id' => $assignee->id, 'year' => 2026, 'deca_no' => 'DC-7']);
        $this->assignProject($recent, $viewer, $assignee, 2);

        $old = Project::factory()->inProgress()->create(['project_code' => 'PRJ-OLD', 'assignee_id' => $assignee->id, 'year' => 2026]);
        $this->assignProject($old, $viewer, $assignee, 15);

        $otherYear = Project::factory()->inProgress()->create(['project_code' => 'PRJ-LASTYEAR', 'assignee_id' => $assignee->id, 'year' => 2025]);
        $this->assignProject($otherYear, $viewer, $assignee, 3);

        $othersProject = Project::factory()->inProgress()->create(['project_code' => 'PRJ-OTHERS', 'assignee_id' => $other->id, 'year' => 2026]);
        $this->assignProject($othersProject, $viewer, $other, 1);

        $this->actingAs($viewer)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('drillDown(', false)
            ->assertSee('dashboard-projects', false);

        $this->actingAs($viewer)
            ->get(route('dashboard.projects', ['bucket' => 'under5', 'year' => 2026, 'assignee_id' => $assignee->id]))
            ->assertOk()
            ->assertSee(route('projects.show', $recent), false)
            ->assertSee('DC-7')
            ->assertSee('Drill Assignee')
            ->assertDontSee('PRJ-OLD')
            ->assertDontSee('PRJ-LASTYEAR')
            ->assertDontSee('PRJ-OTHERS');

        $this->actingAs($viewer)
            ->get(route('dashboard.projects', ['bucket' => 'under5']))
            ->assertOk()
            ->assertSee('PRJ-RECENT')
            ->assertSee('PRJ-LASTYEAR')
            ->assertSee('PRJ-OTHERS')
            ->assertDontSee('PRJ-OLD');

        $this->actingAs($viewer)
            ->get(route('dashboard.projects', ['bucket' => 'over10', 'assignee_id' => $assignee->id]))
            ->assertOk()
            ->assertSee('PRJ-OLD')
            ->assertDontSee('PRJ-RECENT');
    }

    public function test_dashboard_drill_down_reflects_changes_since_the_page_loaded(): void
    {
        $viewer = User::factory()->create();
        $assignee = User::factory()->create();

        $project = Project::factory()->inProgress()->create(['project_code' => 'PRJ-DONE-SINCE', 'assignee_id' => $assignee->id]);
        $this->assignProject($project, $viewer, $assignee, 1);

        $project->update(['status' => ProjectStatus::Completed, 'completed_at' => now()]);

        $this->actingAs($viewer)
            ->get(route('dashboard.projects', ['bucket' => 'under5', 'assignee_id' => $assignee->id]))
            ->assertOk()
            ->assertDontSee('PRJ-DONE-SINCE')
            ->assertSee(__('No projects in this range any more.'));
    }

    public function test_dashboard_drill_down_rejects_an_unknown_bucket(): void
    {
        $viewer = User::factory()->create();

        $this->actingAs($viewer)
            ->getJson(route('dashboard.projects', ['bucket' => 'forever']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('bucket');
    }
}
