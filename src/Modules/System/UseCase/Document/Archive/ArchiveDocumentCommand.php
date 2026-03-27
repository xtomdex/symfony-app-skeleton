<?php

declare(strict_types=1);

namespace App\Modules\System\UseCase\Document\Archive;

final readonly class ArchiveDocumentCommand
{
    public function __construct(
        public string $id,
    ) {}
}
