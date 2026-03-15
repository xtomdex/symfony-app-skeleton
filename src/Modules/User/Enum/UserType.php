<?php

declare(strict_types=1);

namespace App\Modules\User\Enum;

enum UserType: string
{
    case CLIENT = 'client';
    case ADMIN = 'admin';
    case ROOT = 'root';
}
