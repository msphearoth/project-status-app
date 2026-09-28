<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_user_list(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create();

        $this->actingAs($admin)->get(route('users.index'))->assertOk();
    }

    public function test_non_admin_cannot_view_the_user_list(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('users.index'))->assertForbidden();
    }

    public function test_admin_can_create_a_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New Staff',
            'email' => 'new-staff@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::User->value,
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'new-staff@example.com',
            'role' => UserRole::User->value,
        ]);
    }

    public function test_non_admin_cannot_create_a_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('users.store'), [
            'name' => 'New Staff',
            'email' => 'new-staff@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::User->value,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'new-staff@example.com']);
    }

    public function test_admin_can_promote_a_user_to_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => UserRole::Admin->value,
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertSame(UserRole::Admin, $user->fresh()->role);
    }

    public function test_admin_can_reset_a_users_password(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $originalHash = $user->password;

        $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => UserRole::User->value,
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ]);

        $this->assertNotSame($originalHash, $user->fresh()->password);
    }

    public function test_the_last_admin_cannot_be_demoted(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->put(route('users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => UserRole::User->value,
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertSame(UserRole::Admin, $admin->fresh()->role);
    }

    public function test_an_admin_can_be_demoted_when_another_admin_exists(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->put(route('users.update', $otherAdmin), [
            'name' => $otherAdmin->name,
            'email' => $otherAdmin->email,
            'role' => UserRole::User->value,
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertSame(UserRole::User, $otherAdmin->fresh()->role);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete(route('users.destroy', $admin))->assertForbidden();
        $this->assertModelExists($admin);
    }

    public function test_an_admin_can_delete_another_admin_leaving_exactly_one(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete(route('users.destroy', $otherAdmin));

        $this->assertModelMissing($otherAdmin);
        $this->assertSame(1, User::where('role', UserRole::Admin)->count());
    }

    public function test_a_user_with_project_history_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $creator = User::factory()->create();
        Project::factory()->create(['created_by' => $creator->id]);

        $response = $this->actingAs($admin)->delete(route('users.destroy', $creator));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertModelExists($creator);
    }

    public function test_a_user_without_history_can_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'));
        $this->assertModelMissing($user);
    }

    public function test_non_admin_cannot_delete_a_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)->delete(route('users.destroy', $otherUser))->assertForbidden();
        $this->assertModelExists($otherUser);
    }
}
