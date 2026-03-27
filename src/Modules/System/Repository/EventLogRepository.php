<?php

declare(strict_types=1);

namespace App\Modules\System\Repository;

use App\Domain\Pagination\ListCriteria;
use App\Domain\Pagination\PaginatedResult;
use App\Domain\Persistence\AbstractRepository;
use App\Infrastructure\Pagination\PaginatedQueryTrait;
use App\Modules\System\Entity\EventLog;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends AbstractRepository<EventLog>
 */
final class EventLogRepository extends AbstractRepository
{
    use PaginatedQueryTrait;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, EventLog::class);
    }

    public function findPaginated(ListCriteria $criteria): PaginatedResult
    {
        $qb = $this->getRepository()->createQueryBuilder('e');

        return $this->paginate($qb, $criteria, [
            'eventName'  => 'e.eventName',
            'recordedAt' => 'e.recordedAt',
        ]);
    }
}
