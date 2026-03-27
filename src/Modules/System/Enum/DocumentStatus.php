<?php

declare(strict_types=1);

namespace App\Modules\System\Enum;

enum DocumentStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
