<?php

declare(strict_types=1);

namespace App\Modules\User\Enum;

enum UserRole: string
{
    case SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    case ADMIN = 'ROLE_ADMIN';
    case USER = 'ROLE_USER';

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
