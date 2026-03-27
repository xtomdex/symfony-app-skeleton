<?php

declare(strict_types=1);

namespace App\Modules\System\UseCase\Document\Create;

use App\Domain\CommandBus\Exception\ValidationException;
use App\Domain\Eventing\Contract\FlusherInterface;
use App\Modules\System\Entity\Document;
use App\Modules\System\Exception\DocumentSlugAlreadyExistsException;
use App\Modules\System\Repository\DocumentRepository;

final readonly class CreateDocumentHandler
{
    public function __construct(
        private DocumentRepository $repository,
        private FlusherInterface $flusher,
    ) {}

    public function __invoke(CreateDocumentCommand $command): string
    {
        if ($this->repository->existsBySlug($command->slug)) {
            throw new ValidationException(
                DocumentSlugAlreadyExistsException::forSlug($command->slug)->getMessage()
            );
        }

        $document = Document::create(
            $command->id,
            $command->title,
            $command->slug,
            $command->content,
            $command->type,
            $command->description,
        );

        $this->repository->add($document);
        $this->flusher->flush($document);

        return $command->id;
    }
}
