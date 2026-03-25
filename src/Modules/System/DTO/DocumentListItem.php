<?php

declare(strict_types=1);

namespace App\Modules\System\DTO;

use App\Modules\System\Entity\Document;
use App\Modules\System\Enum\DocumentStatus;
use App\Modules\System\Enum\DocumentType;

final readonly class DocumentListItem
{
    public function __construct(
        public string $id,
        public string $title,
        public string $slug,
        public ?string $description,
        public DocumentType $type,
        public DocumentStatus $status,
        public ?\DateTimeImmutable $editedAt,
        public ?\DateTimeImmutable $publishedAt,
        public ?\DateTimeImmutable $createdAt,
    ) {}

    public static function fromEntity(Document $document): self
    {
        return new self(
            id: $document->getId(),
            title: $document->getTitle(),
            slug: $document->getSlug(),
            description: $document->getDescription(),
            type: $document->getType(),
            status: $document->getStatus(),
            editedAt: $document->getEditedAt(),
            publishedAt: $document->getPublishedAt(),
            createdAt: $document->getCreatedAt(),
        );
    }
}
