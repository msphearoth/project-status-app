<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Notifications\ProjectAssigned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProjectAssignedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignee_is_notified_when_a_project_is_assigned(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->create();

        $this->actingAs($admin)->post(route('projects.assign', $project), [
            'assignee_id' => $assignee->id,
        ])->assertRedirect(route('projects.show', $project));

        Notification::assertSentTo($assignee, ProjectAssigned::class, function (ProjectAssigned $notification) use ($project, $admin) {
            return $notification->project->is($project) && $notification->assignedBy->is($admin);
        });
        Notification::assertNotSentTo($admin, ProjectAssigned::class);
    }

    public function test_assignee_is_notified_for_each_bulk_assigned_project(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->create();
        $projects = Project::factory()->count(2)->create(['received_date' => today()->subDays(5)]);

        $this->actingAs($admin)->post(route('projects.bulk-assign'), [
            'project_ids' => $projects->pluck('id')->all(),
            'assignee_id' => $assignee->id,
        ])->assertSessionHasNoErrors();

        Notification::assertSentToTimes($assignee, ProjectAssigned::class, 2);
    }

    public function test_user_is_not_notified_when_assigning_a_project_to_themselves(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $project = Project::factory()->create();

        $this->actingAs($admin)->post(route('projects.assign', $project), [
            'assignee_id' => $admin->id,
        ])->assertRedirect(route('projects.show', $project));

        Notification::assertNothingSent();
    }

    public function test_unread_notifications_are_shown_in_the_navigation(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Sokha']);
        $assignee = User::factory()->create();
        $project = Project::factory()->create(['project_code' => 'PRJ-BELL']);

        $assignee->notify(new ProjectAssigned($project, $admin));

        $this->actingAs($assignee)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Sokha assigned project PRJ-BELL to you.');
    }

    public function test_opening_a_notification_marks_it_as_read_and_shows_the_project(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->create();
        $assignee->notify(new ProjectAssigned($project, $admin));
        $notification = $assignee->notifications()->first();

        $this->actingAs($assignee)
            ->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('projects.show', $project));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_users_cannot_open_notifications_of_other_users(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->create();
        $otherUser = User::factory()->create();
        $assignee->notify(new ProjectAssigned(Project::factory()->create(), $admin));
        $notification = $assignee->notifications()->first();

        $this->actingAs($otherUser)
            ->get(route('notifications.open', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_notifications_are_removed_when_their_project_is_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->create();
        $deleted = Project::factory()->create();
        $kept = Project::factory()->create();
        $assignee->notify(new ProjectAssigned($deleted, $admin));
        $assignee->notify(new ProjectAssigned($kept, $admin));

        $this->actingAs($admin)
            ->delete(route('projects.destroy', $deleted))
            ->assertRedirect(route('projects.index'));

        $this->assertSame([$kept->id], $assignee->notifications()->get()->pluck('data.project_id')->all());
    }

    public function test_notifications_are_removed_when_their_projects_are_bulk_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->create();
        $deleted = Project::factory()->count(2)->create();
        $kept = Project::factory()->create();
        $deleted->push($kept)->each(fn (Project $project) => $assignee->notify(new ProjectAssigned($project, $admin)));

        $this->actingAs($admin)
            ->from(route('projects.index'))
            ->delete(route('projects.bulk-destroy'), ['project_ids' => $deleted->take(2)->pluck('id')->all()])
            ->assertSessionHasNoErrors();

        $this->assertSame([$kept->id], $assignee->notifications()->get()->pluck('data.project_id')->all());
    }

    public function test_all_notifications_can_be_marked_as_read(): void
    {
        $admin = User::factory()->admin()->create();
        $assignee = User::factory()->create();
        $assignee->notify(new ProjectAssigned(Project::factory()->create(), $admin));
        $assignee->notify(new ProjectAssigned(Project::factory()->create(), $admin));

        $this->actingAs($assignee)
            ->from(route('dashboard'))
            ->post(route('notifications.read-all'))
            ->assertRedirect(route('dashboard'));

        $this->assertSame(0, $assignee->unreadNotifications()->count());
    }
}
