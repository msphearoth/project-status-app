<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserPermission;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Determine whether the user may use the given feature. Admins have
     * access to everything; everyone else needs the permission granted.
     */
    public function hasAccessTo(UserPermission $permission): bool
    {
        return $this->isAdmin() || $this->checkPermissionTo($permission->value);
    }

    /**
     * Replace the user's granted permissions. Admins implicitly hold every
     * permission, so nothing is stored for them.
     *
     * @param  list<string>  $permissions
     */
    public function syncAccess(array $permissions): void
    {
        $this->syncPermissions($this->isAdmin() ? [] : $permissions);
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function assignedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'assignee_id');
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'created_by');
    }
}
