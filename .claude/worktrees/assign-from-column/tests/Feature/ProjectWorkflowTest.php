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
            'year' => now()->year,
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

    public function test_creating_a_project_requires_a_valid_year(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'project_code' => 'PRJ-BADYEAR',
            'year' => 1899,
            'work_code' => 'WRK-0003',
            'on_road' => 'Main St',
            'start_road' => '1st Ave',
            'end_road' => '5th Ave',
            'pipe_type' => 'PVC',
            'pipe_diameter' => 100,
            'pipe_length' => 500,
            'received_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('year');
        $this->assertDatabaseMissing('projects', ['project_code' => 'PRJ-BADYEAR']);
    }

    public function test_project_forms_render_the_received_date_picker(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)->get(route('projects.create'))
            ->assertOk()
            ->assertSee('name="received_date"', false)
            ->assertSee('value="'.today()->toDateString().'"', false)
            ->assertSee('DD-MMM-YY');

        $this->actingAs($user)->get(route('projects.edit', $project))
            ->assertOk()
            ->assertSee('value="'.$project->received_date->toDateString().'"', false);

        $this->actingAs($user)->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee($project->received_date->format('d-M-y'));
    }

    public function test_received_date_cannot_be_in_the_future(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'project_code' => 'PRJ-FUTURE',
            'year' => now()->year,
            'work_code' => 'WRK-0004',
            'on_road' => 'Main St',
            'start_road' => '1st Ave',
            'end_road' => '5th Ave',
            'pipe_type' => 'PVC',
            'pipe_diameter' => 100,
            'pipe_length' => 500,
            'received_date' => today()->addDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors([
            'received_date' => 'The received date field must be a date before or equal to '.today()->format('d-M-y').'.',
        ]);
        $this->assertDatabaseMissing('projects', ['project_code' => 'PRJ-FUTURE']);
    }

    public function test_a_regular_user_can_create_a_project(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'project_code' => 'PRJ-0002',
            'year' => now()->year,
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

    public function test_a_regular_user_can_update_their_pending_project_but_only_admins_can_delete_it(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)->put(route('projects.update', $project), [
            'project_code' => $project->project_code,
            'year' => $project->year,
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
            ->assertForbidden();

        $this->assertModelExists($project);

        $this->actingAs(User::factory()->admin()->create())->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertModelMissing($project);
    }

    public function test_a_regular_user_cannot_edit_a_project_assigned_to_someone_else(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->inProgress()->create([
            'created_by' => $creator->id,
            'assignee_id' => $assignee->id,
        ]);

        $this->actingAs($creator)->get(route('projects.edit', $project))->assertForbidden();
        $this->actingAs($assignee)->get(route('projects.edit', $project))->assertOk();
        $this->actingAs(User::factory()->admin()->create())->get(route('projects.edit', $project))->assertOk();
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
        $this->assertSame(today()->toDateString(), $project->latestAssignmentLog->assigned_on->toDateString());
    }

    public function test_assigning_a_project_stores_the_chosen_assigned_date(): void
    {
        $user = User::factory()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->create([
            'created_by' => $user->id,
            'received_date' => today()->subDays(10),
        ]);

        $this->actingAs($user)->post(route('projects.assign', $project), [
            'assignee_id' => $assignee->id,
            'assigned_on' => today()->subDays(3)->toDateString(),
        ])->assertRedirect(route('projects.show', $project));

        $log = $project->fresh()->latestAssignmentLog;

        $this->assertSame(today()->subDays(3)->toDateString(), $log->assigned_on->toDateString());
        $this->assertSame(today()->subDays(3)->toDateString(), $log->assignedDate()->toDateString());
    }

    public function test_assigned_date_cannot_be_in_the_future_or_before_the_received_date(): void
    {
        $user = User::factory()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->create([
            'created_by' => $user->id,
            'received_date' => today()->subDays(5),
        ]);

        foreach ([today()->addDay()->toDateString(), today()->subDays(6)->toDateString(), '26/09/2026'] as $invalidDate) {
            $this->actingAs($user)->post(route('projects.assign', $project), [
                'assignee_id' => $assignee->id,
                'assigned_on' => $invalidDate,
            ])->assertSessionHasErrors('assigned_on');
        }

        $this->assertDatabaseCount('project_assignment_logs', 0);

        $this->actingAs($user)->post(route('projects.assign', $project), [
            'assignee_id' => $assignee->id,
            'assigned_on' => today()->subDays(6)->toDateString(),
        ])->assertSessionHasErrors([
            'assigned_on' => 'The assigned date field must be a date after or equal to '.today()->subDays(5)->format('d-M-y').'.',
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
            'assigned_from' => $firstAssignee->id,
            'assigned_to' => $secondAssignee->id,
        ]);
    }

    public function test_reassigning_stores_the_chosen_assign_from_user(): void
    {
        $user = User::factory()->admin()->create();
        $currentAssignee = User::factory()->create();
        $previousHolder = User::factory()->create();
        $newAssignee = User::factory()->create();
        $project = Project::factory()->inProgress()->create([
            'created_by' => $user->id,
            'assignee_id' => $currentAssignee->id,
        ]);

        $this->actingAs($user)->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee(__('Assign Project From'))
            ->assertSee('confirm-project-unassign', false);

        $this->actingAs($user)->post(route('projects.assign', $project), [
            'assigned_from' => $newAssignee->id,
            'assignee_id' => $newAssignee->id,
        ])->assertSessionHasErrors('assigned_from');

        $this->actingAs($user)->post(route('projects.assign', $project), [
            'assigned_from' => $previousHolder->id,
            'assignee_id' => $newAssignee->id,
        ])->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_assignment_logs', [
            'project_id' => $project->id,
            'action' => 'REASSIGNED',
            'assigned_from' => $previousHolder->id,
            'assigned_to' => $newAssignee->id,
        ]);
    }

    public function test_unassigning_returns_the_project_to_pending_and_logs_it(): void
    {
        $user = User::factory()->admin()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->inProgress()->create([
            'created_by' => $user->id,
            'assignee_id' => $assignee->id,
        ]);

        $this->actingAs($user)->delete(route('projects.unassign', $project))
            ->assertRedirect(route('projects.show', $project));

        $project->refresh();

        $this->assertSame(ProjectStatus::Pending, $project->status);
        $this->assertNull($project->assignee_id);
        $this->assertDatabaseHas('project_assignment_logs', [
            'project_id' => $project->id,
            'action' => 'UNASSIGNED',
            'assigned_by' => $user->id,
            'assigned_from' => $assignee->id,
            'assigned_to' => null,
        ]);

        $this->actingAs($user)->get(route('projects.show', $project))
            ->assertOk()
            ->assertDontSee(__('Assign Project From'))
            ->assertDontSee('confirm-project-unassign', false);
    }

    public function test_pending_or_completed_projects_cannot_be_unassigned(): void
    {
        $user = User::factory()->admin()->create();
        $pending = Project::factory()->create(['created_by' => $user->id]);
        $completed = Project::factory()->completed()->create(['created_by' => $user->id]);

        $this->actingAs($user)->delete(route('projects.unassign', $pending))->assertForbidden();
        $this->actingAs($user)->delete(route('projects.unassign', $completed))->assertForbidden();
    }

    public function test_a_regular_user_cannot_reassign_complete_or_unassign_a_project_assigned_to_someone_else(): void
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
            ->assertForbidden();

        $this->actingAs($bystander)
            ->post(route('projects.complete', $project), [
                'project_amount' => 1000,
                'request_number' => 'REQ-1',
            ])
            ->assertForbidden();

        $this->actingAs($assignee)->delete(route('projects.unassign', $project))->assertForbidden();

        $this->actingAs($bystander)->get(route('projects.show', $project))
            ->assertOk()
            ->assertDontSee(__('Reassign Project'))
            ->assertDontSee(route('projects.complete', $project), false);

        $project->refresh();
        $this->assertSame($assignee->id, $project->assignee_id);
        $this->assertSame(ProjectStatus::InProgress, $project->status);
    }

    public function test_admin_can_reassign_and_complete_a_project_assigned_to_someone_else(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->create();
        $newAssignee = User::factory()->create();
        $project = Project::factory()->inProgress()->create([
            'created_by' => $admin->id,
            'assignee_id' => $assignee->id,
        ]);

        $this->actingAs($admin)
            ->post(route('projects.assign', $project), ['assignee_id' => $newAssignee->id])
            ->assertRedirect(route('projects.show', $project));

        $this->assertSame($newAssignee->id, $project->fresh()->assignee_id);

        $this->actingAs($admin)
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
