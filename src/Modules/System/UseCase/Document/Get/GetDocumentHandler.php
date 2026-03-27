<?php

declare(strict_types=1);

namespace App\Modules\System\UseCase\Document\Get;

use App\Domain\CommandBus\Exception\NotFoundException;
use App\Modules\System\DTO\DocumentView;
use App\Modules\System\Enum\DocumentLookupField;
use App\Modules\System\Exception\DocumentNotFoundException;
use App\Modules\System\Repository\DocumentRepository;

final readonly class GetDocumentHandler
{
    public function __construct(
        private DocumentRepository $repository,
    ) {}

    public function __invoke(GetDocumentQuery $query): DocumentView
    {
        $document = match ($query->field) {
            DocumentLookupField::Id   => $this->repository->findById($query->value),
            DocumentLookupField::Slug => $this->repository->findBySlug($query->value),
        };

        if ($document === null) {
            throw new NotFoundException(
                match ($query->field) {
                    DocumentLookupField::Id   => DocumentNotFoundException::byId($query->value)->getMessage(),
                    DocumentLookupField::Slug => DocumentNotFoundException::bySlug($query->value)->getMessage(),
                }
            );
        }

        return DocumentView::fromEntity($document);
    }
}
