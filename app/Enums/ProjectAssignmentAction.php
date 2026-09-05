<?php

namespace App\Enums;

enum ProjectAssignmentAction: string
{
    case Assigned = 'ASSIGNED';
    case Reassigned = 'REASSIGNED';
    case Completed = 'COMPLETED';

    public function label(): string
    {
        return match ($this) {
            self::Assigned => __('Assigned'),
            self::Reassigned => __('Reassigned'),
            self::Completed => __('Completed'),
        };
    }
}
