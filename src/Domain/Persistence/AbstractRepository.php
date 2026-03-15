<?php

declare(strict_types=1);

namespace App\Domain\Persistence;

use App\Domain\Persistence\Contract\EntityInterface;
use App\Domain\Persistence\Contract\RepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\EntityRepository;

abstract class AbstractRepository implements RepositoryInterface
{
    private EntityRepository $repository;
    private Connection $connection;

    public function __construct(
        protected EntityManagerInterface $em,
        protected string $entityClass
    ) {
        $this->repository = $em->getRepository($this->entityClass);
        $this->connection = $em->getConnection();
    }

    public function get(string $id): EntityInterface
    {
        /** @var EntityInterface $entity */
        if (!$entity = $this->getRepository()->find($id)) {
            throw new EntityNotFoundException(sprintf('%s is not found.', $this->getEntityName()));
        }

        return $entity;
    }

    public function add(EntityInterface $entity): void
    {
        $this->getEntityManager()->persist($entity);
    }

    public function remove(EntityInterface $entity): void
    {
        $this->getEntityManager()->remove($entity);
    }

    public function getEntityName(): string
    {
        $parts = explode('\\', $this->entityClass);
        return end($parts);
    }

    protected function getConnection(): Connection
    {
        return $this->connection;
    }

    protected function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }
}
