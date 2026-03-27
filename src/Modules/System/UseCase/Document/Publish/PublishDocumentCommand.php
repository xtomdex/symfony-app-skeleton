<?php

declare(strict_types=1);

namespace App\Modules\System\UseCase\Document\Publish;

final readonly class PublishDocumentCommand
{
    public function __construct(
        public string $id,
    ) {}
}
