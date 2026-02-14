<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum ResponseErrorCode: string
{
    case Validation = 'validation_failed';
    case NotFound = 'not_found';
    case Unauthorized = 'unauthorized';
    case Forbidden = 'forbidden';
    case Internal = 'internal_error';
}
