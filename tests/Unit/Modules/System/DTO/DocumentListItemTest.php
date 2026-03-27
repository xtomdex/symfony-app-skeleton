<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\DTO;

use App\Modules\System\DTO\DocumentListItem;
use App\Modules\System\Entity\Document;
use App\Modules\System\Enum\DocumentStatus;
use App\Modules\System\Enum\DocumentType;
use PHPUnit\Framework\TestCase;

final class DocumentListItemTest extends TestCase
{
    public function test_from_entity(): void
    {
        $document = Document::create(
            id: 'test-id-1',
            title: 'My Title',
            slug: 'my-title',
            content: 'Full content.',
            type: DocumentType::Page,
            description: 'A description.',
        );

        $item = DocumentListItem::fromEntity($document);

        self::assertSame($document->getId(), $item->id);
        self::assertSame($document->getTitle(), $item->title);
        self::assertSame($document->getSlug(), $item->slug);
        self::assertSame($document->getDescription(), $item->description);
        self::assertSame($document->getType(), $item->type);
        self::assertSame($document->getStatus(), $item->status);
        self::assertSame($document->getEditedAt(), $item->editedAt);
        self::assertSame($document->getPublishedAt(), $item->publishedAt);
        self::assertSame($document->getCreatedAt(), $item->createdAt);
        self::assertSame(DocumentStatus::Draft, $item->status);
        self::assertSame(DocumentType::Page, $item->type);
        self::assertFalse(property_exists($item, 'content'));
    }
}
