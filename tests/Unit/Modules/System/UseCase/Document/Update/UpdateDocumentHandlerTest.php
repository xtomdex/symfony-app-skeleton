<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\UseCase\Document\Update;

use App\Domain\CommandBus\Exception\NotFoundException;
use App\Domain\Eventing\Contract\FlusherInterface;
use App\Modules\System\Entity\Document;
use App\Modules\System\Enum\DocumentType;
use App\Modules\System\Repository\DocumentRepository;
use App\Modules\System\UseCase\Document\Update\UpdateDocumentCommand;
use App\Modules\System\UseCase\Document\Update\UpdateDocumentHandler;
use PHPUnit\Framework\TestCase;

final class UpdateDocumentHandlerTest extends TestCase
{
    public function test_updates_document(): void
    {
        $document = Document::create('doc-id', 'Old Title', 'my-slug', 'Old content.', DocumentType::Page);

        $repository = $this->createStub(DocumentRepository::class);
        $repository->method('findById')->willReturn($document);

        $flusher = $this->createMock(FlusherInterface::class);
        $flusher->expects($this->once())->method('flush');

        $handler = new UpdateDocumentHandler($repository, $flusher);
        $command = new UpdateDocumentCommand(
            id: 'doc-id',
            title: 'New Title',
            content: 'New content.',
            description: 'New description.',
        );

        ($handler)($command);

        self::assertSame('New Title', $document->getTitle());
        self::assertSame('New content.', $document->getContent());
        self::assertSame('New description.', $document->getDescription());
    }

    public function test_throws_not_found(): void
    {
        $repository = $this->createStub(DocumentRepository::class);
        $repository->method('findById')->willReturn(null);

        $flusher = $this->createStub(FlusherInterface::class);

        $handler = new UpdateDocumentHandler($repository, $flusher);
        $command = new UpdateDocumentCommand(id: 'missing-id', title: 'Title', content: 'Content.');

        $this->expectException(NotFoundException::class);

        ($handler)($command);
    }
}
