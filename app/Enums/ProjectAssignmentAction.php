<?php

namespace App\Enums;

enum ProjectAssignmentAction: string
{
    case Assigned = 'ASSIGNED';
    case Reassigned = 'REASSIGNED';
    case Unassigned = 'UNASSIGNED';
    case Completed = 'COMPLETED';
    case Reopened = 'REOPENED';

    public function label(): string
    {
        return match ($this) {
            self::Assigned => __('Assigned'),
            self::Reassigned => __('Reassigned'),
            self::Unassigned => __('Unassigned'),
            self::Completed => __('Completed'),
            self::Reopened => __('Reopened'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Assigned => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20',
            self::Reassigned => 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-600/20 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/20',
            self::Unassigned => 'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-500/20 dark:bg-gray-500/10 dark:text-gray-300 dark:ring-gray-500/20',
            self::Completed => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
            self::Reopened => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20',
        };
    }

    /**
     * The icon shown for this action on the assignment history timeline.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Assigned => 'user-plus',
            self::Reassigned => 'arrows-right-left',
            self::Unassigned => 'user-minus',
            self::Completed => 'check-circle',
            self::Reopened => 'arrow-uturn-left',
        };
    }
}
