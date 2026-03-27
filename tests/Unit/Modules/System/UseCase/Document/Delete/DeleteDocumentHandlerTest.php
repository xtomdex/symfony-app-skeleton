<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\UseCase\Document\Delete;

use App\Domain\CommandBus\Exception\NotFoundException;
use App\Domain\Eventing\Contract\FlusherInterface;
use App\Modules\System\Entity\Document;
use App\Modules\System\Enum\DocumentType;
use App\Modules\System\Repository\DocumentRepository;
use App\Modules\System\UseCase\Document\Delete\DeleteDocumentCommand;
use App\Modules\System\UseCase\Document\Delete\DeleteDocumentHandler;
use PHPUnit\Framework\TestCase;

final class DeleteDocumentHandlerTest extends TestCase
{
    public function test_deletes_document(): void
    {
        $document = Document::create('doc-id', 'Title', 'my-slug', 'Content.', DocumentType::Page);

        $repository = $this->createMock(DocumentRepository::class);
        $repository->method('findById')->willReturn($document);
        $repository->expects($this->once())->method('remove')->with($document);

        $flusher = $this->createMock(FlusherInterface::class);
        $flusher->expects($this->once())->method('flush');

        $handler = new DeleteDocumentHandler($repository, $flusher);
        $command = new DeleteDocumentCommand(id: 'doc-id');

        ($handler)($command);
    }

    public function test_throws_not_found(): void
    {
        $repository = $this->createStub(DocumentRepository::class);
        $repository->method('findById')->willReturn(null);

        $flusher = $this->createStub(FlusherInterface::class);

        $handler = new DeleteDocumentHandler($repository, $flusher);
        $command = new DeleteDocumentCommand(id: 'missing-id');

        $this->expectException(NotFoundException::class);

        ($handler)($command);
    }
}
