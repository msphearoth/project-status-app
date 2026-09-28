<?php

namespace Tests\Feature;

use App\Enums\ProjectAssignmentAction;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Tests\TestCase;

class ProjectsIndexFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_index_filters_by_assignee(): void
    {
        $user = User::factory()->create();
        $firstAssignee = User::factory()->create();
        $secondAssignee = User::factory()->create();

        Project::factory()->inProgress()->create([
            'created_by' => $user->id,
            'assignee_id' => $firstAssignee->id,
            'project_code' => 'PRJ-FIRST',
        ]);

        Project::factory()->inProgress()->create([
            'created_by' => $user->id,
            'assignee_id' => $secondAssignee->id,
            'project_code' => 'PRJ-SECOND',
        ]);

        $response = $this->actingAs($user)->get(route('projects.index', ['assignee_id' => $firstAssignee->id]));

        $response->assertOk();
        $response->assertSee('PRJ-FIRST');
        $response->assertDontSee('PRJ-SECOND');
    }

    public function test_projects_index_shows_row_actions_including_delete_for_admins(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('projects.index'));

        $response->assertOk();
        $response->assertSee(route('projects.show', $project), false);
        $response->assertSee(route('projects.edit', $project), false);
        $response->assertSee(Js::from(route('projects.destroy', $project))->toHtml(), false);
    }

    public function test_projects_index_hides_delete_and_others_edit_for_standard_users(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownProject = Project::factory()->create(['created_by' => $user->id]);
        $othersProject = Project::factory()->inProgress()->create([
            'created_by' => $otherUser->id,
            'assignee_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->get(route('projects.index'));

        $response->assertOk();
        $response->assertSee(route('projects.show', $othersProject), false);
        $response->assertSee(route('projects.edit', $ownProject), false);
        $response->assertDontSee(route('projects.edit', $othersProject), false);
        $response->assertDontSee(Js::from(route('projects.destroy', $ownProject))->toHtml(), false);
    }

    public function test_projects_index_shows_who_the_project_was_assigned_from(): void
    {
        $user = User::factory()->create();
        $previousHolder = User::factory()->create(['name' => 'Previous Holder']);
        $currentAssignee = User::factory()->create();

        $project = Project::factory()->inProgress()->create([
            'created_by' => $user->id,
            'assignee_id' => $currentAssignee->id,
        ]);

        $project->assignmentLogs()->create([
            'action' => ProjectAssignmentAction::Reassigned,
            'assigned_by' => $user->id,
            'assigned_from' => $previousHolder->id,
            'assigned_to' => $currentAssignee->id,
        ]);

        $response = $this->actingAs($user)->get(route('projects.index'));

        $response->assertOk();
        $response->assertSeeInOrder([__('Work Code'), __('Assign Project From'), __('Status')]);
        $response->assertDontSee(__('On Road'));
        $response->assertSee('Previous Holder');
    }
}
