<?php

use App\Enums\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Create the permission for reopening completed projects. No standard user
 * gets it by default; admins hold it implicitly.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('permissions')->insertOrIgnore([
            'name' => UserPermission::ReopenProjects->value,
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')
            ->where('guard_name', 'web')
            ->where('name', UserPermission::ReopenProjects->value)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
