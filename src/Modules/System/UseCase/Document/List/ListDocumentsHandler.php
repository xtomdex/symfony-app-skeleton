<?php

declare(strict_types=1);

namespace App\Modules\System\UseCase\Document\List;

use App\Domain\Pagination\PaginatedResult;
use App\Modules\System\DTO\DocumentListItem;
use App\Modules\System\Repository\DocumentRepository;

final readonly class ListDocumentsHandler
{
    public function __construct(
        private DocumentRepository $repository,
    ) {}

    public function __invoke(ListDocumentsQuery $query): PaginatedResult
    {
        return $this->repository->findPaginated($query->criteria)
            ->map(DocumentListItem::fromEntity(...));
    }
}
