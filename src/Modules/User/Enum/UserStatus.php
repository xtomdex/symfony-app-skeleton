<?php

declare(strict_types=1);

namespace App\Modules\User\Enum;

enum UserStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Inactive = 'inactive';
    case Blocked = 'blocked';
}
