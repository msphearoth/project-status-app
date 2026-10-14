<?php

use App\Enums\UserPermission;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Create every feature permission and grant existing standard users the
 * default set, so nobody loses access they had before permissions existed.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        DB::table('permissions')->insertOrIgnore(array_map(fn (UserPermission $permission) => [
            'name' => $permission->value,
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ], UserPermission::cases()));

        $defaultPermissionIds = DB::table('permissions')
            ->where('guard_name', 'web')
            ->whereIn('name', UserPermission::valuesOf(UserPermission::standardUserDefaults()))
            ->pluck('id');

        DB::table('users')
            ->where('role', '!=', UserRole::Admin->value)
            ->pluck('id')
            ->each(fn (int $userId) => DB::table('model_has_permissions')->insertOrIgnore(
                $defaultPermissionIds->map(fn (int $permissionId) => [
                    'permission_id' => $permissionId,
                    'model_type' => User::class,
                    'model_id' => $userId,
                ])->all(),
            ));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')
            ->where('guard_name', 'web')
            ->whereIn('name', UserPermission::valuesOf(UserPermission::cases()))
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
