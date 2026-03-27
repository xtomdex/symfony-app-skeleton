<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\UseCase\Document\Publish;

use App\Domain\CommandBus\Exception\NotFoundException;
use App\Domain\Eventing\Contract\FlusherInterface;
use App\Modules\System\Entity\Document;
use App\Modules\System\Enum\DocumentStatus;
use App\Modules\System\Enum\DocumentType;
use App\Modules\System\Repository\DocumentRepository;
use App\Modules\System\UseCase\Document\Publish\PublishDocumentCommand;
use App\Modules\System\UseCase\Document\Publish\PublishDocumentHandler;
use PHPUnit\Framework\TestCase;

final class PublishDocumentHandlerTest extends TestCase
{
    public function test_publishes_document(): void
    {
        $document = Document::create('doc-id', 'Title', 'my-slug', 'Content.', DocumentType::Page);

        $repository = $this->createStub(DocumentRepository::class);
        $repository->method('findById')->willReturn($document);

        $flusher = $this->createMock(FlusherInterface::class);
        $flusher->expects($this->once())->method('flush');

        $handler = new PublishDocumentHandler($repository, $flusher);
        $command = new PublishDocumentCommand(id: 'doc-id');

        ($handler)($command);

        self::assertSame(DocumentStatus::Published, $document->getStatus());
        self::assertNotNull($document->getPublishedAt());
    }

    public function test_throws_not_found(): void
    {
        $repository = $this->createStub(DocumentRepository::class);
        $repository->method('findById')->willReturn(null);

        $flusher = $this->createStub(FlusherInterface::class);

        $handler = new PublishDocumentHandler($repository, $flusher);
        $command = new PublishDocumentCommand(id: 'missing-id');

        $this->expectException(NotFoundException::class);

        ($handler)($command);
    }
}
