<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Pending = 'PENDING';
    case InProgress = 'IN_PROGRESS';
    case Completed = 'COMPLETED';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::InProgress => __('In Progress'),
            self::Completed => __('Completed'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20',
            self::InProgress => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20',
            self::Completed => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
        };
    }
}
