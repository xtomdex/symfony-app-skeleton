<?php

declare(strict_types=1);

namespace App\Modules\System\UseCase\Document\Create;

use App\Modules\System\Enum\DocumentType;

final readonly class CreateDocumentCommand
{
    public function __construct(
        public string $id,
        public string $title,
        public string $slug,
        public string $content,
        public DocumentType $type,
        public ?string $description = null,
    ) {}
}
