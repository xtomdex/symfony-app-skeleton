<?php

declare(strict_types=1);

namespace App\Modules\System\Enum;

enum DocumentLookupField: string
{
    case Id = 'id';
    case Slug = 'slug';
}
