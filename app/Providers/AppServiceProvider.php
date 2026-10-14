<?php

namespace App\Providers;

use App\Enums\UserPermission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            $permission = UserPermission::tryFrom($ability);

            return $permission ? $user->hasAccessTo($permission) : null;
        });
    }
}
