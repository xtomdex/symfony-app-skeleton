<?php

declare(strict_types=1);

namespace App\Domain\Persistence\Contract;

interface EntityInterface
{
    public function getId(): string;
}
