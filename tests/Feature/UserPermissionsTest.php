<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\UserPermission;
use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_edit_page_shows_each_permission_toggle(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->withPermissions([UserPermission::DeleteProjects])->create();

        $response = $this->actingAs($admin)->get(route('users.edit', $user));

        $response->assertOk();
        foreach (UserPermission::cases() as $permission) {
            $response->assertSee('value="'.$permission->value.'"', false);
        }
    }

    public function test_the_create_page_shows_each_permission_toggle(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('users.create'));

        $response->assertOk();
        foreach (UserPermission::cases() as $permission) {
            $response->assertSee('value="'.$permission->value.'"', false);
        }
    }

    public function test_admin_can_create_a_user_with_chosen_permissions(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New Staff',
            'email' => 'new-staff@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::User->value,
            'permissions' => [UserPermission::ViewProjects->value, UserPermission::DeleteProjects->value],
        ])->assertRedirect(route('users.index'));

        $user = User::where('email', 'new-staff@example.com')->sole();
        $this->assertEqualsCanonicalizing(
            [UserPermission::ViewProjects->value, UserPermission::DeleteProjects->value],
            $user->getPermissionNames()->all(),
        );
    }

    public function test_admin_can_grant_and_revoke_a_users_permissions(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => UserRole::User->value,
            'permissions' => [UserPermission::ViewProjects->value, UserPermission::UnassignProjects->value],
        ])->assertRedirect(route('users.index'));

        $this->assertEqualsCanonicalizing(
            [UserPermission::ViewProjects->value, UserPermission::UnassignProjects->value],
            $user->fresh()->getPermissionNames()->all(),
        );
    }

    public function test_submitting_no_permissions_revokes_them_all(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => UserRole::User->value,
        ]);

        $this->assertCount(0, $user->fresh()->getPermissionNames());
    }

    public function test_promoting_to_admin_clears_stored_permissions_but_grants_full_access(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => UserRole::Admin->value,
            'permissions' => [UserPermission::ViewProjects->value],
        ]);

        $user->refresh();
        $this->assertCount(0, $user->getPermissionNames());
        $this->assertTrue($user->hasAccessTo(UserPermission::DeleteProjects));
    }

    public function test_unknown_permissions_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => UserRole::User->value,
            'permissions' => ['users.manage'],
        ])->assertSessionHasErrors('permissions.0');
    }

    public function test_non_admin_cannot_change_permissions(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => UserRole::User->value,
            'permissions' => [UserPermission::DeleteProjects->value],
        ])->assertForbidden();

        $this->assertFalse($user->fresh()->hasAccessTo(UserPermission::DeleteProjects));
    }

    public function test_without_view_permission_a_user_only_sees_their_own_projects(): void
    {
        $user = User::factory()->withPermissions([])->create();
        $assigned = Project::factory()->inProgress()->create(['assignee_id' => $user->id]);
        $other = Project::factory()->create();

        $this->actingAs($user)->get(route('projects.index'))->assertForbidden();
        $this->actingAs($user)->get(route('projects.show', $assigned))->assertOk();
        $this->actingAs($user)->get(route('projects.show', $other))->assertForbidden();
    }

    public function test_feature_routes_require_their_permission(): void
    {
        $user = User::factory()->withPermissions([UserPermission::ViewProjects])->create();

        $this->actingAs($user)->get(route('projects.export'))->assertForbidden();
        $this->actingAs($user)->get(route('dashboard.export'))->assertForbidden();
        $this->actingAs($user)->get(route('projects.create'))->assertForbidden();
        $this->actingAs($user)->get(route('projects.import.create'))->assertForbidden();
    }

    public function test_granted_feature_routes_are_allowed(): void
    {
        $user = User::factory()->withPermissions([
            UserPermission::ExportProjects,
            UserPermission::ExportDashboard,
            UserPermission::ImportProjects,
        ])->create();

        $this->actingAs($user)->get(route('projects.export'))->assertOk();
        $this->actingAs($user)->get(route('dashboard.export'))->assertOk();
        $this->actingAs($user)->get(route('projects.import.create'))->assertOk();
        $this->actingAs($user)->get(route('projects.create'))->assertForbidden();
    }

    public function test_a_user_without_assign_permission_cannot_assign_pending_projects(): void
    {
        $user = User::factory()->withPermissions([UserPermission::ViewProjects])->create();
        $assignee = User::factory()->create();
        $project = Project::factory()->create();

        $this->actingAs($user)->post(route('projects.assign', $project), [
            'assignee_id' => $assignee->id,
        ])->assertForbidden();

        $this->assertSame(ProjectStatus::Pending, $project->fresh()->status);
    }

    public function test_a_user_granted_delete_permission_can_delete_projects(): void
    {
        $user = User::factory()->withPermissions([UserPermission::DeleteProjects])->create();
        $project = Project::factory()->create();

        $this->actingAs($user)->delete(route('projects.destroy', $project))->assertRedirect();

        $this->assertModelMissing($project);
    }

    public function test_a_user_granted_unassign_permission_can_unassign_projects(): void
    {
        $user = User::factory()->withPermissions([UserPermission::UnassignProjects])->create();
        $project = Project::factory()->inProgress()->create();

        $this->actingAs($user)->delete(route('projects.unassign', $project))->assertRedirect();

        $this->assertSame(ProjectStatus::Pending, $project->fresh()->status);
    }

    public function test_manage_all_permission_allows_editing_projects_assigned_to_others(): void
    {
        $user = User::factory()->withPermissions([UserPermission::ManageAllProjects])->create();
        $project = Project::factory()->inProgress()->create();

        $this->actingAs($user)->get(route('projects.edit', $project))->assertOk();
    }
}
