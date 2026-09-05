<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_project_as_pending(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('projects.store'), [
            'project_code' => 'PRJ-0001',
            'work_code' => 'WRK-0001',
            'on_road' => 'Main St',
            'start_road' => '1st Ave',
            'end_road' => '5th Ave',
            'pipe_type' => 'PVC',
            'pipe_diameter' => 100,
            'pipe_length' => 500,
            'received_date' => now()->toDateString(),
        ]);

        $project = Project::firstWhere('project_code', 'PRJ-0001');

        $response->assertRedirect(route('projects.show', $project));
        $this->assertSame(ProjectStatus::Pending, $project->status);
        $this->assertNull($project->assignee_id);
    }

    public function test_a_regular_user_can_create_a_project(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'project_code' => 'PRJ-0002',
            'work_code' => 'WRK-0002',
            'on_road' => 'Main St',
            'start_road' => '1st Ave',
            'end_road' => '5th Ave',
            'pipe_type' => 'PVC',
            'pipe_diameter' => 100,
            'pipe_length' => 500,
            'received_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', ['project_code' => 'PRJ-0002']);
    }

    public function test_a_regular_user_can_update_and_delete_a_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)->put(route('projects.update', $project), [
            'project_code' => $project->project_code,
            'work_code' => 'WRK-UPDATED',
            'on_road' => $project->on_road,
            'start_road' => $project->start_road,
            'end_road' => $project->end_road,
            'pipe_type' => $project->pipe_type,
            'pipe_diameter' => $project->pipe_diameter,
            'pipe_length' => $project->pipe_length,
            'received_date' => $project->received_date->toDateString(),
        ])->assertRedirect(route('projects.show', $project));

        $this->assertSame('WRK-UPDATED', $project->fresh()->work_code);

        $this->actingAs($user)->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertModelMissing($project);
    }

    public function test_admin_can_assign_a_pending_project_which_moves_it_in_progress(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->create(['created_by' => $admin->id]);

        $response = $this->actingAs($admin)->post(route('projects.assign', $project), [
            'assignee_id' => $assignee->id,
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $project->refresh();

        $this->assertSame(ProjectStatus::InProgress, $project->status);
        $this->assertSame($assignee->id, $project->assignee_id);
        $this->assertDatabaseHas('project_assignment_logs', [
            'project_id' => $project->id,
            'action' => 'ASSIGNED',
            'assigned_by' => $admin->id,
            'assigned_to' => $assignee->id,
        ]);
    }

    public function test_assignee_can_reassign_an_in_progress_project_to_another_user(): void
    {
        $admin = User::factory()->admin()->create();
        $firstAssignee = User::factory()->create();
        $secondAssignee = User::factory()->create();
        $project = Project::factory()->inProgress()->create([
            'created_by' => $admin->id,
            'assignee_id' => $firstAssignee->id,
        ]);

        $response = $this->actingAs($firstAssignee)->post(route('projects.assign', $project), [
            'assignee_id' => $secondAssignee->id,
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $project->refresh();

        $this->assertSame(ProjectStatus::InProgress, $project->status);
        $this->assertSame($secondAssignee->id, $project->assignee_id);
        $this->assertDatabaseHas('project_assignment_logs', [
            'project_id' => $project->id,
            'action' => 'REASSIGNED',
            'assigned_by' => $firstAssignee->id,
            'assigned_to' => $secondAssignee->id,
        ]);
    }

    public function test_any_user_can_reassign_or_complete_a_project_they_are_not_assigned_to(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->create();
        $bystander = User::factory()->create();
        $project = Project::factory()->inProgress()->create([
            'created_by' => $admin->id,
            'assignee_id' => $assignee->id,
        ]);

        $this->actingAs($bystander)
            ->post(route('projects.assign', $project), ['assignee_id' => $bystander->id])
            ->assertRedirect(route('projects.show', $project));

        $this->assertSame($bystander->id, $project->fresh()->assignee_id);

        $this->actingAs($bystander)
            ->post(route('projects.complete', $project), [
                'project_amount' => 1000,
                'request_number' => 'REQ-1',
            ])
            ->assertRedirect(route('projects.show', $project));

        $this->assertSame(ProjectStatus::Completed, $project->fresh()->status);
    }

    public function test_assignee_can_complete_a_project_with_amount_and_request_number(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->inProgress()->create([
            'created_by' => $admin->id,
            'assignee_id' => $assignee->id,
        ]);

        $response = $this->actingAs($assignee)->post(route('projects.complete', $project), [
            'project_amount' => 12345.67,
            'request_number' => 'REQ-9999',
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $project->refresh();

        $this->assertSame(ProjectStatus::Completed, $project->status);
        $this->assertSame('12345.67', $project->project_amount);
        $this->assertSame('REQ-9999', $project->request_number);
        $this->assertNotNull($project->completed_at);
        $this->assertDatabaseHas('project_assignment_logs', [
            'project_id' => $project->id,
            'action' => 'COMPLETED',
            'assigned_by' => $assignee->id,
        ]);
    }

    public function test_completing_a_project_requires_amount_and_request_number(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->inProgress()->create([
            'created_by' => $admin->id,
            'assignee_id' => $assignee->id,
        ]);

        $response = $this->actingAs($assignee)->post(route('projects.complete', $project), []);

        $response->assertSessionHasErrors(['project_amount', 'request_number']);
        $this->assertSame(ProjectStatus::InProgress, $project->fresh()->status);
    }

    public function test_a_completed_project_cannot_be_assigned_or_completed_again(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->completed()->create([
            'created_by' => $admin->id,
            'assignee_id' => $assignee->id,
        ]);

        $this->actingAs($admin)
            ->post(route('projects.assign', $project), ['assignee_id' => $assignee->id])
            ->assertForbidden();

        $this->actingAs($assignee)
            ->post(route('projects.complete', $project), [
                'project_amount' => 100,
                'request_number' => 'REQ-2',
            ])
            ->assertForbidden();
    }
}
