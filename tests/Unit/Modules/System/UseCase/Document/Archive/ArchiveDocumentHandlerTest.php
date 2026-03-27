<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\UseCase\Document\Archive;

use App\Domain\CommandBus\Exception\NotFoundException;
use App\Domain\Eventing\Contract\FlusherInterface;
use App\Modules\System\Entity\Document;
use App\Modules\System\Enum\DocumentStatus;
use App\Modules\System\Enum\DocumentType;
use App\Modules\System\Repository\DocumentRepository;
use App\Modules\System\UseCase\Document\Archive\ArchiveDocumentCommand;
use App\Modules\System\UseCase\Document\Archive\ArchiveDocumentHandler;
use PHPUnit\Framework\TestCase;

final class ArchiveDocumentHandlerTest extends TestCase
{
    public function test_archives_document(): void
    {
        $document = Document::create('doc-id', 'Title', 'my-slug', 'Content.', DocumentType::Page);

        $repository = $this->createStub(DocumentRepository::class);
        $repository->method('findById')->willReturn($document);

        $flusher = $this->createMock(FlusherInterface::class);
        $flusher->expects($this->once())->method('flush');

        $handler = new ArchiveDocumentHandler($repository, $flusher);
        $command = new ArchiveDocumentCommand(id: 'doc-id');

        ($handler)($command);

        self::assertSame(DocumentStatus::Archived, $document->getStatus());
    }

    public function test_throws_not_found(): void
    {
        $repository = $this->createStub(DocumentRepository::class);
        $repository->method('findById')->willReturn(null);

        $flusher = $this->createStub(FlusherInterface::class);

        $handler = new ArchiveDocumentHandler($repository, $flusher);
        $command = new ArchiveDocumentCommand(id: 'missing-id');

        $this->expectException(NotFoundException::class);

        ($handler)($command);
    }
}
