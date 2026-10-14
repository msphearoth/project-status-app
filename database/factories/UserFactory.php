<?php

namespace Database\Factories;

use App\Enums\UserPermission;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Give standard users the default permission set, as creating them
     * through the admin UI would.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            if (! $user->isAdmin()) {
                $user->givePermissionTo(UserPermission::valuesOf(UserPermission::standardUserDefaults()));
            }
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::User,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin,
        ]);
    }

    /**
     * Indicate that the user should hold exactly the given permissions.
     *
     * @param  list<UserPermission>  $permissions
     */
    public function withPermissions(array $permissions): static
    {
        return $this->afterCreating(
            fn (User $user) => $user->syncPermissions(UserPermission::valuesOf($permissions)),
        );
    }
}
