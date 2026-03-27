<?php

declare(strict_types=1);

namespace App\Modules\System\Event;

use App\Domain\Eventing\Contract\DomainEventInterface;

final readonly class DocumentUpdated implements DomainEventInterface
{
    public function __construct(
        public string $documentId,
    ) {}

    public function getPayload(): array
    {
        return [
            'documentId' => $this->documentId,
        ];
    }
}
