<?php

declare(strict_types=1);

namespace App\Tests\Unit\Pagination;

use App\Domain\Pagination\ListCriteria;
use App\Domain\Pagination\PaginatedResult;
use App\Infrastructure\Pagination\PaginatedQueryTrait;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Note: Full coverage of PaginatedQueryTrait is achieved through functional tests
 * that use real Doctrine repositories against the test database.
 * These unit tests verify the whitelist guard and input sanitization in isolation.
 */
final class PaginatedQueryTraitTest extends TestCase
{
    private object $sut;

    protected function setUp(): void
    {
        $this->sut = new class {
            use PaginatedQueryTrait;

            public function run(QueryBuilder $qb, ListCriteria $criteria, array $sortableFields = []): PaginatedResult
            {
                return $this->paginate($qb, $criteria, $sortableFields);
            }
        };
    }

    /**
     * Build a QueryBuilder mock suitable for paginate().
     *
     * The trait clones the QB to run a COUNT query, so:
     * - clone.getQuery() → $countQuery (first getQuery() call across both instances)
     * - original.getQuery() → $itemsQuery (second call)
     *
     * select() / resetDQLPart() must return self so that clone.select() stays type-safe.
     */
    private function buildQb(Query $countQuery, Query $itemsQuery): QueryBuilder
    {
        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('getRootAliases')->willReturn(['u']);
        $qb->method('select')->willReturnSelf();
        $qb->method('resetDQLPart')->willReturnSelf();
        $qb->method('setFirstResult')->willReturnSelf();
        $qb->method('setMaxResults')->willReturnSelf();
        // First getQuery() invocation is the COUNT (on clone), second is items (on original)
        $qb->method('getQuery')->willReturnOnConsecutiveCalls($countQuery, $itemsQuery);

        return $qb;
    }

    public function test_sort_field_not_in_whitelist_does_not_apply_ordering(): void
    {
        $countQuery = $this->createStub(Query::class);
        $countQuery->method('getSingleScalarResult')->willReturn(5);

        $itemsQuery = $this->createStub(Query::class);
        $itemsQuery->method('getResult')->willReturn([]);

        $qb = $this->buildQb($countQuery, $itemsQuery);

        // orderBy must NOT be called when sortField is not in the whitelist
        $qb->expects($this->never())->method('orderBy');

        $criteria = new ListCriteria(page: 1, pageSize: 10, sortField: 'injected_field', sortDirection: 'asc');

        $result = $this->sut->run($qb, $criteria, ['name' => 'u.name', 'email' => 'u.email']);

        $this->assertInstanceOf(PaginatedResult::class, $result);
    }

    public function test_page_below_one_is_clamped_to_one(): void
    {
        $countQuery = $this->createStub(Query::class);
        $countQuery->method('getSingleScalarResult')->willReturn(3);

        $itemsQuery = $this->createStub(Query::class);
        $itemsQuery->method('getResult')->willReturn(['x', 'y', 'z']);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('getRootAliases')->willReturn(['u']);
        $qb->method('select')->willReturnSelf();
        $qb->method('resetDQLPart')->willReturnSelf();
        $qb->method('setMaxResults')->willReturnSelf();
        $qb->method('getQuery')->willReturnOnConsecutiveCalls($countQuery, $itemsQuery);

        // page clamped to 1 → offset = (1-1)*pageSize = 0
        $qb->expects($this->once())->method('setFirstResult')->with(0)->willReturnSelf();

        $result = $this->sut->run($qb, new ListCriteria(page: -5, pageSize: 10));

        $this->assertSame(1, $result->currentPage());
    }

    public function test_page_size_above_100_is_clamped_to_100(): void
    {
        $countQuery = $this->createStub(Query::class);
        $countQuery->method('getSingleScalarResult')->willReturn(0);

        $itemsQuery = $this->createStub(Query::class);
        $itemsQuery->method('getResult')->willReturn([]);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('getRootAliases')->willReturn(['u']);
        $qb->method('select')->willReturnSelf();
        $qb->method('resetDQLPart')->willReturnSelf();
        $qb->method('setFirstResult')->willReturnSelf();
        $qb->method('getQuery')->willReturnOnConsecutiveCalls($countQuery, $itemsQuery);

        // pageSize must be clamped to 100
        $qb->expects($this->once())->method('setMaxResults')->with(100)->willReturnSelf();

        $result = $this->sut->run($qb, new ListCriteria(page: 1, pageSize: 999));

        $this->assertSame(100, $result->pageSize());
    }
}
