<?php

declare(strict_types=1);

namespace App\Modules\User\Enum;

enum UserStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case BLOCKED = 'blocked';
}
