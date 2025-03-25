<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_USER = 'super_user';
    case REGULAR_USER = 'regular_user';

    public function text()
    {
        return match ($this) {
            self::SUPER_USER => 'Super user',
            self::REGULAR_USER => 'Regular usere'
        };
    }
}
