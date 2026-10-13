<?php

namespace Tests\Feature;

use App\Enums\ProjectAssignmentAction;
use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectBulkCompleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_complete_selected_in_progress_projects_in_one_request(): void
    {
        $admin = User::factory()->admin()->create();
        $projects = Project::factory()->inProgress()->count(2)->create();
        $untouched = Project::factory()->inProgress()->create();

        $this->actingAs($admin)
            ->postJson(route('projects.bulk-complete'), ['project_ids' => $projects->pluck('id')->all(), 'request_number' => 'REQ-100'])
            ->assertOk()
            ->assertJson(['message' => '2 projects marked as completed.'])
            ->assertSessionHas('status', '2 projects marked as completed.');

        foreach ($projects as $project) {
            $project->refresh();
            $this->assertSame(ProjectStatus::Completed, $project->status);
            $this->assertSame('REQ-100', $project->request_number);
            $this->assertNotNull($project->completed_at);
            $this->assertDatabaseHas('project_assignment_logs', [
                'project_id' => $project->id,
                'action' => ProjectAssignmentAction::Completed->value,
                'assigned_by' => $admin->id,
                'assigned_to' => $project->assignee_id,
            ]);
        }

        $this->assertSame(ProjectStatus::InProgress, $untouched->refresh()->status);
    }

    public function test_assignee_can_bulk_complete_only_their_own_projects(): void
    {
        $user = User::factory()->create();
        $own = Project::factory()->inProgress()->create(['assignee_id' => $user->id]);
        $others = Project::factory()->inProgress()->create(['project_code' => 'OTHER-1']);

        $this->actingAs($user)
            ->postJson(route('projects.bulk-complete'), ['project_ids' => [$own->id, $others->id], 'request_number' => 'REQ-100'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['project_ids' => 'OTHER-1']);

        $this->assertSame(ProjectStatus::InProgress, $own->refresh()->status);
        $this->assertSame(ProjectStatus::InProgress, $others->refresh()->status);

        $this->actingAs($user)
            ->postJson(route('projects.bulk-complete'), ['project_ids' => [$own->id], 'request_number' => 'REQ-100'])
            ->assertOk();

        $this->assertSame(ProjectStatus::Completed, $own->refresh()->status);
    }

    public function test_pending_or_completed_projects_cannot_be_bulk_completed(): void
    {
        $admin = User::factory()->admin()->create();
        $pending = Project::factory()->create();
        $completed = Project::factory()->completed()->create();

        $this->actingAs($admin)
            ->postJson(route('projects.bulk-complete'), ['project_ids' => [$pending->id, $completed->id], 'request_number' => 'REQ-100'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['project_ids']);

        $this->assertSame(ProjectStatus::Pending, $pending->refresh()->status);
        $this->assertDatabaseMissing('project_assignment_logs', ['action' => ProjectAssignmentAction::Completed->value]);
    }

    public function test_a_selection_is_required(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->postJson(route('projects.bulk-complete'), ['project_ids' => [], 'request_number' => 'REQ-100'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['project_ids']);
    }

    public function test_a_request_number_is_required(): void
    {
        $project = Project::factory()->inProgress()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->postJson(route('projects.bulk-complete'), ['project_ids' => [$project->id], 'request_number' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['request_number']);

        $this->assertSame(ProjectStatus::InProgress, $project->refresh()->status);
    }

    public function test_bulk_complete_button_is_shown_only_when_the_user_can_complete_a_listed_project(): void
    {
        $user = User::factory()->create();
        Project::factory()->inProgress()->create();

        $this->actingAs($user)
            ->get(route('projects.index'))
            ->assertDontSee('Bulk Complete');

        $own = Project::factory()->inProgress()->create(['assignee_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('projects.index'))
            ->assertSee('Bulk Complete')
            ->assertSee('value="'.$own->id.'" x-model="selected"', false);
    }
}
