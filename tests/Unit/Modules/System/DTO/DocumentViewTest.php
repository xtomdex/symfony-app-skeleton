<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\DTO;

use App\Modules\System\DTO\DocumentView;
use App\Modules\System\Entity\Document;
use App\Modules\System\Enum\DocumentStatus;
use App\Modules\System\Enum\DocumentType;
use PHPUnit\Framework\TestCase;

final class DocumentViewTest extends TestCase
{
    public function test_from_entity(): void
    {
        $document = Document::create(
            id: 'test-id-1',
            title: 'My Title',
            slug: 'my-title',
            content: 'Full content.',
            type: DocumentType::Legal,
            description: 'A description.',
        );

        $view = DocumentView::fromEntity($document);

        self::assertSame($document->getId(), $view->id);
        self::assertSame($document->getTitle(), $view->title);
        self::assertSame($document->getSlug(), $view->slug);
        self::assertSame($document->getDescription(), $view->description);
        self::assertSame($document->getContent(), $view->content);
        self::assertSame($document->getType(), $view->type);
        self::assertSame($document->getStatus(), $view->status);
        self::assertSame($document->getEditedAt(), $view->editedAt);
        self::assertSame($document->getPublishedAt(), $view->publishedAt);
        self::assertSame($document->getCreatedAt(), $view->createdAt);
        self::assertSame($document->getUpdatedAt(), $view->updatedAt);
        self::assertSame(DocumentStatus::Draft, $view->status);
        self::assertSame(DocumentType::Legal, $view->type);
    }
}
