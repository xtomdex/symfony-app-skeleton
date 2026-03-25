<?php

declare(strict_types=1);

namespace App\Modules\User\Enum;

enum UserType: string
{
    case Client = 'client';
    case Admin = 'admin';
    case Root = 'root';
}
