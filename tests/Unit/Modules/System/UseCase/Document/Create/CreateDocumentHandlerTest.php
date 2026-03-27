<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\UseCase\Document\Create;

use App\Domain\CommandBus\Exception\ValidationException;
use App\Domain\Eventing\Contract\FlusherInterface;
use App\Modules\System\Enum\DocumentType;
use App\Modules\System\Repository\DocumentRepository;
use App\Modules\System\UseCase\Document\Create\CreateDocumentCommand;
use App\Modules\System\UseCase\Document\Create\CreateDocumentHandler;
use PHPUnit\Framework\TestCase;

final class CreateDocumentHandlerTest extends TestCase
{
    public function test_creates_document_successfully(): void
    {
        $repository = $this->createMock(DocumentRepository::class);
        $repository->method('existsBySlug')->willReturn(false);
        $repository->expects($this->once())->method('add');

        $flusher = $this->createMock(FlusherInterface::class);
        $flusher->expects($this->once())->method('flush');

        $handler = new CreateDocumentHandler($repository, $flusher);
        $command = new CreateDocumentCommand(
            id: 'doc-id-1',
            title: 'Test Document',
            slug: 'test-document',
            content: 'Content here.',
            type: DocumentType::Page,
        );

        $result = ($handler)($command);

        self::assertSame('doc-id-1', $result);
    }

    public function test_throws_validation_when_slug_exists(): void
    {
        $repository = $this->createStub(DocumentRepository::class);
        $repository->method('existsBySlug')->willReturn(true);

        $flusher = $this->createStub(FlusherInterface::class);

        $handler = new CreateDocumentHandler($repository, $flusher);
        $command = new CreateDocumentCommand(
            id: 'doc-id-1',
            title: 'Test Document',
            slug: 'existing-slug',
            content: 'Content here.',
            type: DocumentType::Page,
        );

        $this->expectException(ValidationException::class);

        ($handler)($command);
    }
}
