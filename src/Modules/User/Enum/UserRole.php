<?php

declare(strict_types=1);

namespace App\Modules\User\Enum;

enum UserRole: string
{
    case SuperAdmin = 'ROLE_SUPER_ADMIN';
    case Admin = 'ROLE_ADMIN';
    case User = 'ROLE_USER';

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
