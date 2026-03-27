<?php

declare(strict_types=1);

namespace App\Modules\System\Event;

use App\Domain\Eventing\Contract\DomainEventInterface;
use App\Modules\System\Enum\DocumentType;

final readonly class DocumentDeleted implements DomainEventInterface
{
    public function __construct(
        public string $documentId,
        public string $slug,
        public DocumentType $type,
    ) {}

    public function getPayload(): array
    {
        return [
            'documentId' => $this->documentId,
            'slug'       => $this->slug,
            'type'       => $this->type->value,
        ];
    }
}
