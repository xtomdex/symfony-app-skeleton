<?php

declare(strict_types=1);

namespace App\Domain\Persistence\Contract;

interface RepositoryInterface
{
    public function get(string $id): EntityInterface;
    public function add(EntityInterface $entity): void;
    public function remove(EntityInterface $entity): void;
    public function getEntityName(): string;
}
