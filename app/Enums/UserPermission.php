<?php

namespace App\Enums;

/**
 * Feature-level permissions an admin can grant to or revoke from individual
 * users. Admins implicitly hold every permission; managing users is never
 * delegated and stays admin-only.
 */
enum UserPermission: string
{
    case ExportDashboard = 'dashboard.export';
    case ViewProjects = 'projects.view';
    case ExportProjects = 'projects.export';
    case CreateProjects = 'projects.create';
    case ImportProjects = 'projects.import';
    case AssignProjects = 'projects.assign';
    case ManageAllProjects = 'projects.manage-all';
    case UnassignProjects = 'projects.unassign';
    case ReopenProjects = 'projects.reopen';
    case DeleteProjects = 'projects.delete';

    public function label(): string
    {
        return match ($this) {
            self::ExportDashboard => __('Export dashboard'),
            self::ViewProjects => __('View projects'),
            self::ExportProjects => __('Export projects'),
            self::CreateProjects => __('Create projects'),
            self::ImportProjects => __('Import projects'),
            self::AssignProjects => __('Assign pending projects'),
            self::ManageAllProjects => __('Manage all projects'),
            self::UnassignProjects => __('Unassign projects'),
            self::ReopenProjects => __('Reopen completed projects'),
            self::DeleteProjects => __('Delete projects'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ExportDashboard => __('Download the dashboard summary as an Excel file.'),
            self::ViewProjects => __('See the project list and every project\'s details.'),
            self::ExportProjects => __('Download the project list as an Excel file.'),
            self::CreateProjects => __('Add new projects one at a time.'),
            self::ImportProjects => __('Create many projects at once from a spreadsheet.'),
            self::AssignProjects => __('Assign pending projects to any user.'),
            self::ManageAllProjects => __('Edit, reassign and complete projects assigned to anyone.'),
            self::UnassignProjects => __('Remove the assignee and return a project to pending.'),
            self::ReopenProjects => __('Revert a completed project back to in progress.'),
            self::DeleteProjects => __('Permanently delete projects.'),
        };
    }

    /**
     * The module the permission is grouped under in the admin UI.
     */
    public function module(): PermissionModule
    {
        return match ($this) {
            self::ExportDashboard => PermissionModule::Dashboard,
            default => PermissionModule::Projects,
        };
    }

    /**
     * Whether granting the permission lets the user change or remove other
     * people's work, so the admin UI flags it for a second look.
     */
    public function isSensitive(): bool
    {
        return in_array($this, [
            self::ManageAllProjects,
            self::UnassignProjects,
            self::ReopenProjects,
            self::DeleteProjects,
        ], true);
    }

    /**
     * @param  list<self>  $permissions
     * @return list<string>
     */
    public static function valuesOf(array $permissions): array
    {
        return array_column($permissions, 'value');
    }

    /**
     * The permissions a newly created standard user starts with, matching what
     * standard users could do before permissions became configurable.
     *
     * @return list<self>
     */
    public static function standardUserDefaults(): array
    {
        return [
            self::ExportDashboard,
            self::ViewProjects,
            self::ExportProjects,
            self::CreateProjects,
            self::ImportProjects,
            self::AssignProjects,
        ];
    }
}
