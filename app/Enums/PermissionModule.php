<?php

namespace App\Enums;

/**
 * The areas of the app that UserPermission entries are grouped under in the
 * admin permission editor.
 */
enum PermissionModule: string
{
    case Dashboard = 'dashboard';
    case Projects = 'projects';

    public function label(): string
    {
        return match ($this) {
            self::Dashboard => __('Dashboard'),
            self::Projects => __('Projects'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Dashboard => __('The overview of in-progress work.'),
            self::Projects => __('Creating, assigning and maintaining projects.'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Dashboard => 'squares',
            self::Projects => 'folder',
        };
    }

    /**
     * The permissions in this module, in display order.
     *
     * @return list<UserPermission>
     */
    public function permissions(): array
    {
        return array_values(array_filter(
            UserPermission::cases(),
            fn (UserPermission $permission) => $permission->module() === $this,
        ));
    }
}
