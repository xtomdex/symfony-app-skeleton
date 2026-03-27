<?php

declare(strict_types=1);

namespace App\Modules\System\UseCase\Document\Delete;

final readonly class DeleteDocumentCommand
{
    public function __construct(
        public string $id,
    ) {}
}
