<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\IdGeneratorInterface;
use Ramsey\Uuid\Uuid;

final class UuidGenerator implements IdGeneratorInterface
{
    public function generate(): string
    {
        return Uuid::uuid4()->toString();
    }
}
