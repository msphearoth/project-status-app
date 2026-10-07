<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Admin => __('Admin'),
            self::User => __('Standard User'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Admin => 'bg-violet-50 text-violet-700 ring-1 ring-inset ring-violet-600/20 dark:bg-violet-500/10 dark:text-violet-400 dark:ring-violet-500/20',
            self::User => 'bg-gray-50 text-gray-700 ring-1 ring-inset ring-gray-500/20 dark:bg-gray-500/10 dark:text-gray-300 dark:ring-gray-500/20',
        };
    }
}
