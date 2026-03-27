<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\UseCase\Document\List;

use App\Domain\Pagination\ListCriteria;
use App\Domain\Pagination\PaginatedResult;
use App\Modules\System\DTO\DocumentListItem;
use App\Modules\System\Entity\Document;
use App\Modules\System\Enum\DocumentType;
use App\Modules\System\Repository\DocumentRepository;
use App\Modules\System\UseCase\Document\List\ListDocumentsHandler;
use App\Modules\System\UseCase\Document\List\ListDocumentsQuery;
use PHPUnit\Framework\TestCase;

final class ListDocumentsHandlerTest extends TestCase
{
    public function test_returns_paginated_result(): void
    {
        $document = Document::create('doc-id', 'Title', 'my-slug', 'Content.', DocumentType::Page);
        $paginated = new PaginatedResult([$document], 1, 1, 20, 1);

        $repository = $this->createStub(DocumentRepository::class);
        $repository->method('findPaginated')->willReturn($paginated);

        $handler = new ListDocumentsHandler($repository);
        $query = new ListDocumentsQuery(new ListCriteria());

        $result = ($handler)($query);

        self::assertInstanceOf(PaginatedResult::class, $result);
        self::assertCount(1, $result->items());
        self::assertInstanceOf(DocumentListItem::class, $result->items()[0]);
        self::assertSame('doc-id', $result->items()[0]->id);
    }
}
