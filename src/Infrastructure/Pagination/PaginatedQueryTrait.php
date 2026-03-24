<?php

declare(strict_types=1);

namespace App\Infrastructure\Pagination;

use App\Domain\Pagination\ListCriteria;
use App\Domain\Pagination\PaginatedResult;
use Doctrine\ORM\QueryBuilder;

/**
 * Pagination/sorting helper for Doctrine repositories.
 *
 * Usage in repository:
 *
 *     use PaginatedQueryTrait;
 *
 *     public function findPaginated(ListCriteria $criteria): PaginatedResult
 *     {
 *         $qb = $this->createQueryBuilder('u')
 *             ->where('u.status = :status')
 *             ->setParameter('status', 'active');
 *
 *         return $this->paginate($qb, $criteria);
 *     }
 */
trait PaginatedQueryTrait
{
    /**
     * @template T
     * @param QueryBuilder $qb         QueryBuilder with filters already applied, without sorting/pagination
     * @param ListCriteria $criteria   Pagination and sorting parameters
     * @param array<string, string> $sortableFields  Allowed sort fields map: query param name => DQL field
     *                                               Example: ['name' => 'u.name', 'email' => 'u.email']
     *                                               If empty, sorting is skipped regardless of criteria.
     * @return PaginatedResult<T>
     */
    protected function paginate(
        QueryBuilder $qb,
        ListCriteria $criteria,
        array $sortableFields = [],
    ): PaginatedResult {
        // Sanitize input
        $page = max(1, $criteria->page);
        $pageSize = min(max(1, $criteria->pageSize), 100);

        // Count total items (clone QB before modifying)
        $countQb = clone $qb;
        $countQb->select(sprintf('COUNT(%s)', $countQb->getRootAliases()[0]));
        // Reset ordering on count query for performance
        $countQb->resetDQLPart('orderBy');
        $totalItems = (int) $countQb->getQuery()->getSingleScalarResult();

        // Calculate total pages
        $totalPages = max(1, (int) ceil($totalItems / $pageSize));

        // Clamp page to valid range
        $page = min($page, $totalPages);

        // Apply sorting
        $direction = strtolower($criteria->sortDirection) === 'desc' ? 'DESC' : 'ASC';
        if ($criteria->sortField !== null && isset($sortableFields[$criteria->sortField])) {
            $qb->orderBy($sortableFields[$criteria->sortField], $direction);
        }

        // Apply pagination
        $qb->setFirstResult(($page - 1) * $pageSize);
        $qb->setMaxResults($pageSize);

        return new PaginatedResult(
            items: $qb->getQuery()->getResult(),
            currentPage: $page,
            totalPages: $totalPages,
            pageSize: $pageSize,
            totalItems: $totalItems,
        );
    }
}
