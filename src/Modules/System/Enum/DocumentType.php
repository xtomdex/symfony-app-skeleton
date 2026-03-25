<?php

declare(strict_types=1);

namespace App\Modules\System\Enum;

enum DocumentType: string
{
    case LEGAL = 'legal';
    case PAGE = 'page';
}
