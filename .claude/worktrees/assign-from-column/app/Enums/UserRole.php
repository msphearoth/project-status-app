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
            self::Admin => 'bg-purple-100 text-purple-800 dark:bg-purple-800/30 dark:text-purple-400',
            self::User => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        };
    }
}
