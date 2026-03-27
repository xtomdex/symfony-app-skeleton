<?php

declare(strict_types=1);

namespace App\Modules\System\UseCase\Document\Update;

final readonly class UpdateDocumentCommand
{
    public function __construct(
        public string $id,
        public string $title,
        public string $content,
        public ?string $description = null,
    ) {}
}
