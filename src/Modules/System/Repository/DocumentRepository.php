<?php

declare(strict_types=1);

namespace App\Modules\System\Repository;

use App\Domain\Pagination\ListCriteria;
use App\Domain\Pagination\PaginatedResult;
use App\Domain\Persistence\AbstractRepository;
use App\Infrastructure\Pagination\PaginatedQueryTrait;
use App\Modules\System\Entity\Document;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends AbstractRepository<Document>
 */
final class DocumentRepository extends AbstractRepository
{
    use PaginatedQueryTrait;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, Document::class);
    }

    public function findPaginated(ListCriteria $criteria): PaginatedResult
    {
        $qb = $this->getRepository()->createQueryBuilder('d');

        return $this->paginate($qb, $criteria, [
            'title'     => 'd.title',
            'createdAt' => 'd.createdAt',
            'editedAt'  => 'd.editedAt',
            'status'    => 'd.status',
            'type'      => 'd.type',
        ]);
    }

    public function findBySlug(string $slug): ?Document
    {
        return $this->getRepository()->findOneBy(['slug' => $slug]);
    }

    public function findById(string $id): ?Document
    {
        return $this->getRepository()->find($id);
    }

    public function existsBySlug(string $slug): bool
    {
        return $this->getRepository()->count(['slug' => $slug]) > 0;
    }
}
