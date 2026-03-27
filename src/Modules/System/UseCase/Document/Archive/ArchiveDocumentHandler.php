<?php

declare(strict_types=1);

namespace App\Modules\System\UseCase\Document\Archive;

use App\Domain\CommandBus\Exception\NotFoundException;
use App\Domain\Eventing\Contract\FlusherInterface;
use App\Modules\System\Exception\DocumentNotFoundException;
use App\Modules\System\Repository\DocumentRepository;

final readonly class ArchiveDocumentHandler
{
    public function __construct(
        private DocumentRepository $repository,
        private FlusherInterface $flusher,
    ) {}

    public function __invoke(ArchiveDocumentCommand $command): void
    {
        $document = $this->repository->findById($command->id);

        if ($document === null) {
            throw new NotFoundException(
                DocumentNotFoundException::byId($command->id)->getMessage()
            );
        }

        $document->archive();

        $this->flusher->flush($document);
    }
}
