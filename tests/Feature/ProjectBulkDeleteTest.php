<?php

namespace Tests\Feature;

use App\Enums\ProjectAssignmentAction;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectBulkDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_selected_projects_with_their_assignment_history(): void
    {
        $admin = User::factory()->admin()->create();
        $pending = Project::factory()->create();
        $inProgress = Project::factory()->inProgress()->create();
        $completed = Project::factory()->completed()->create();
        $kept = Project::factory()->create();
        $inProgress->assignmentLogs()->create([
            'action' => ProjectAssignmentAction::Assigned,
            'assigned_by' => $admin->id,
            'assigned_to' => $inProgress->assignee_id,
        ]);

        $this->actingAs($admin)
            ->from(route('projects.index'))
            ->delete(route('projects.bulk-destroy'), [
                'project_ids' => [$pending->id, $inProgress->id, $completed->id],
            ])
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('status', '3 projects deleted successfully.');

        $this->assertModelMissing($pending);
        $this->assertModelMissing($inProgress);
        $this->assertModelMissing($completed);
        $this->assertModelExists($kept);
        $this->assertDatabaseMissing('project_assignment_logs', ['project_id' => $inProgress->id]);
    }

    public function test_regular_user_cannot_bulk_delete_projects(): void
    {
        $user = User::factory()->create();
        $projects = Project::factory()->count(2)->create();

        $this->actingAs($user)
            ->from(route('projects.index'))
            ->delete(route('projects.bulk-destroy'), [
                'project_ids' => $projects->pluck('id')->all(),
            ])
            ->assertRedirect(route('projects.index'))
            ->assertSessionHasErrorsIn('bulkDelete', ['project_ids']);

        $this->assertSame(2, Project::count());
    }

    public function test_a_selection_is_required(): void
    {
        $admin = User::factory()->admin()->create();
        Project::factory()->create();

        $this->actingAs($admin)
            ->from(route('projects.index'))
            ->delete(route('projects.bulk-destroy'), ['project_ids' => []])
            ->assertSessionHasErrorsIn('bulkDelete', ['project_ids']);

        $this->assertSame(1, Project::count());
    }

    public function test_admin_can_select_completed_projects_for_deletion(): void
    {
        $completed = Project::factory()->completed()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('projects.index'))
            ->assertSee('value="'.$completed->id.'" x-model="selected"', false);
    }

    public function test_delete_selected_button_is_shown_only_to_admins(): void
    {
        Project::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('projects.index'))
            ->assertSee('Delete Selected')
            ->assertSee(route('projects.bulk-destroy'));

        $this->actingAs(User::factory()->create())
            ->get(route('projects.index'))
            ->assertDontSee('Delete Selected')
            ->assertDontSee(route('projects.bulk-destroy'));
    }
}
