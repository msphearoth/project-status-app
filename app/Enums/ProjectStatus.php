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
            self::Pending => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/30 dark:text-yellow-400',
            self::InProgress => 'bg-blue-100 text-blue-800 dark:bg-blue-800/30 dark:text-blue-400',
            self::Completed => 'bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-400',
        };
    }
}
