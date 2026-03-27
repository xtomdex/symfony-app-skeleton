<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\UseCase\Document\Get;

use App\Domain\CommandBus\Exception\NotFoundException;
use App\Modules\System\DTO\DocumentView;
use App\Modules\System\Entity\Document;
use App\Modules\System\Enum\DocumentLookupField;
use App\Modules\System\Enum\DocumentType;
use App\Modules\System\Repository\DocumentRepository;
use App\Modules\System\UseCase\Document\Get\GetDocumentHandler;
use App\Modules\System\UseCase\Document\Get\GetDocumentQuery;
use PHPUnit\Framework\TestCase;

final class GetDocumentHandlerTest extends TestCase
{
    public function test_returns_document_view_by_id(): void
    {
        $document = Document::create('doc-id', 'Title', 'my-slug', 'Content.', DocumentType::Page);

        $repository = $this->createStub(DocumentRepository::class);
        $repository->method('findById')->willReturn($document);

        $handler = new GetDocumentHandler($repository);
        $query = new GetDocumentQuery(DocumentLookupField::Id, 'doc-id');

        $result = ($handler)($query);

        self::assertInstanceOf(DocumentView::class, $result);
        self::assertSame('doc-id', $result->id);
        self::assertSame('my-slug', $result->slug);
    }

    public function test_returns_document_view_by_slug(): void
    {
        $document = Document::create('doc-id', 'Title', 'my-slug', 'Content.', DocumentType::Page);

        $repository = $this->createStub(DocumentRepository::class);
        $repository->method('findBySlug')->willReturn($document);

        $handler = new GetDocumentHandler($repository);
        $query = new GetDocumentQuery(DocumentLookupField::Slug, 'my-slug');

        $result = ($handler)($query);

        self::assertInstanceOf(DocumentView::class, $result);
        self::assertSame('my-slug', $result->slug);
    }

    public function test_throws_not_found_by_id(): void
    {
        $repository = $this->createStub(DocumentRepository::class);
        $repository->method('findById')->willReturn(null);

        $handler = new GetDocumentHandler($repository);
        $query = new GetDocumentQuery(DocumentLookupField::Id, 'missing-id');

        $this->expectException(NotFoundException::class);

        ($handler)($query);
    }

    public function test_throws_not_found_by_slug(): void
    {
        $repository = $this->createStub(DocumentRepository::class);
        $repository->method('findBySlug')->willReturn(null);

        $handler = new GetDocumentHandler($repository);
        $query = new GetDocumentQuery(DocumentLookupField::Slug, 'missing-slug');

        $this->expectException(NotFoundException::class);

        ($handler)($query);
    }
}
