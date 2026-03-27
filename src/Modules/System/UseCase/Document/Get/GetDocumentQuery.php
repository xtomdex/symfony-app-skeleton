<?php

declare(strict_types=1);

namespace App\Modules\System\UseCase\Document\Get;

use App\Modules\System\Enum\DocumentLookupField;

final readonly class GetDocumentQuery
{
    public function __construct(
        public DocumentLookupField $field,
        public string $value,
    ) {}
}
