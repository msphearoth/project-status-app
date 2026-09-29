<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectBulkAssignTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_bulk_assign_pending_and_reassign_in_progress_projects(): void
    {
        $admin = User::factory()->admin()->create();
        $previousAssignee = User::factory()->create();
        $newAssignee = User::factory()->create();
        $pending = Project::factory()->create(['received_date' => today()->subDays(10)]);
        $inProgress = Project::factory()->inProgress()->create([
            'assignee_id' => $previousAssignee->id,
            'received_date' => today()->subDays(10),
        ]);

        $this->actingAs($admin)
            ->from(route('projects.index'))
            ->post(route('projects.bulk-assign'), [
                'project_ids' => [$pending->id, $inProgress->id],
                'assignee_id' => $newAssignee->id,
                'assigned_on' => today()->subDays(2)->toDateString(),
                'note' => 'Batch handover',
            ])
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('status', '2 projects assigned successfully.');

        foreach ([$pending, $inProgress] as $project) {
            $project->refresh();
            $this->assertSame(ProjectStatus::InProgress, $project->status);
            $this->assertSame($newAssignee->id, $project->assignee_id);
            $this->assertSame(today()->subDays(2)->toDateString(), $project->latestAssignmentLog->assigned_on->toDateString());
        }

        $this->assertDatabaseHas('project_assignment_logs', [
            'project_id' => $pending->id,
            'action' => 'ASSIGNED',
            'assigned_by' => $admin->id,
            'assigned_from' => null,
            'assigned_to' => $newAssignee->id,
            'note' => 'Batch handover',
        ]);
        $this->assertDatabaseHas('project_assignment_logs', [
            'project_id' => $inProgress->id,
            'action' => 'REASSIGNED',
            'assigned_from' => $previousAssignee->id,
            'assigned_to' => $newAssignee->id,
        ]);
    }

    public function test_bulk_assign_is_rejected_when_any_project_cannot_be_assigned_by_the_user(): void
    {
        $user = User::factory()->create();
        $assignee = User::factory()->create();
        $pending = Project::factory()->create();
        $othersProject = Project::factory()->inProgress()->create(['project_code' => 'PRJ-OTHER']);
        $completed = Project::factory()->completed()->create(['project_code' => 'PRJ-DONE']);

        $this->actingAs($user)
            ->post(route('projects.bulk-assign'), [
                'project_ids' => [$pending->id, $othersProject->id, $completed->id],
                'assignee_id' => $assignee->id,
            ])
            ->assertSessionHasErrorsIn('bulkAssign', [
                'project_ids' => 'You are not allowed to assign: PRJ-DONE, PRJ-OTHER.',
            ]);

        $this->assertSame(ProjectStatus::Pending, $pending->fresh()->status);
        $this->assertDatabaseCount('project_assignment_logs', 0);
    }

    public function test_bulk_assign_rejects_projects_already_assigned_to_the_chosen_user(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->create();
        $pending = Project::factory()->create();
        $alreadyTheirs = Project::factory()->inProgress()->create([
            'project_code' => 'PRJ-SAME',
            'assignee_id' => $assignee->id,
        ]);

        $this->actingAs($admin)
            ->post(route('projects.bulk-assign'), [
                'project_ids' => [$pending->id, $alreadyTheirs->id],
                'assignee_id' => $assignee->id,
            ])
            ->assertSessionHasErrorsIn('bulkAssign', [
                'project_ids' => 'Already assigned to the selected user: PRJ-SAME.',
            ]);

        $this->assertDatabaseCount('project_assignment_logs', 0);
    }

    public function test_bulk_assigned_date_cannot_be_in_the_future_or_before_any_received_date(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->create();
        $older = Project::factory()->create(['received_date' => today()->subDays(10)]);
        $newer = Project::factory()->create([
            'project_code' => 'PRJ-NEW',
            'received_date' => today()->subDays(2),
        ]);

        $this->actingAs($admin)
            ->post(route('projects.bulk-assign'), [
                'project_ids' => [$older->id, $newer->id],
                'assignee_id' => $assignee->id,
                'assigned_on' => today()->addDay()->toDateString(),
            ])
            ->assertSessionHasErrorsIn('bulkAssign', 'assigned_on');

        $this->actingAs($admin)
            ->post(route('projects.bulk-assign'), [
                'project_ids' => [$older->id, $newer->id],
                'assignee_id' => $assignee->id,
                'assigned_on' => today()->subDays(5)->toDateString(),
            ])
            ->assertSessionHasErrorsIn('bulkAssign', [
                'assigned_on' => 'The assigned date cannot be before the received date of: PRJ-NEW.',
            ]);

        $this->assertDatabaseCount('project_assignment_logs', 0);
    }

    public function test_bulk_assign_requires_projects_and_an_assignee(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('projects.bulk-assign'), ['project_ids' => []])
            ->assertSessionHasErrorsIn('bulkAssign', ['project_ids', 'assignee_id']);
    }

    public function test_index_shows_selection_checkboxes_only_for_assignable_projects(): void
    {
        $user = User::factory()->create();
        $pending = Project::factory()->create();
        $othersProject = Project::factory()->inProgress()->create();

        $this->actingAs($user)->get(route('projects.index'))
            ->assertOk()
            ->assertSee('id="bulk-assign-form"', false)
            ->assertSee('name="project_ids[]" value="'.$pending->id.'"', false)
            ->assertDontSee('name="project_ids[]" value="'.$othersProject->id.'"', false);
    }
}
