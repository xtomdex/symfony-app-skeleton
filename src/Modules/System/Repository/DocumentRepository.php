<?php

declare(strict_types=1);

namespace App\Modules\System\Repository;

use App\Domain\Persistence\AbstractRepository;
use App\Modules\System\Entity\Document;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends AbstractRepository<Document>
 */
final class DocumentRepository extends AbstractRepository
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, Document::class);
    }
}
