<?php

declare(strict_types=1);

namespace App\Modules\System\UseCase\Document\List;

use App\Domain\Pagination\ListCriteria;

final readonly class ListDocumentsQuery
{
    public function __construct(
        public ListCriteria $criteria,
    ) {}
}
