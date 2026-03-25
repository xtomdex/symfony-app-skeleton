<?php

declare(strict_types=1);

namespace App\Modules\System\Enum;

enum DocumentStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
