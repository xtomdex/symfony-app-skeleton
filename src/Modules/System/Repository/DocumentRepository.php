<?php

declare(strict_types=1);

namespace App\Modules\System\Repository;

use App\Domain\Pagination\ListCriteria;
use App\Domain\Pagination\PaginatedResult;
use App\Domain\Persistence\AbstractRepository;
use App\Domain\Persistence\Contract\EntityInterface;
use App\Infrastructure\Pagination\PaginatedQueryTrait;
use App\Modules\System\Entity\Document;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends AbstractRepository<Document>
 */
class DocumentRepository extends AbstractRepository
{
    use PaginatedQueryTrait;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, Document::class);
    }

    public function remove(EntityInterface $entity): void
    {
        if (!$entity instanceof Document) {
            throw new \InvalidArgumentException(
                sprintf('Expected %s, got %s.', Document::class, get_class($entity))
            );
        }
        $entity->delete();
        parent::remove($entity);
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
