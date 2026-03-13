<?php

declare(strict_types=1);

namespace App\Domain;

interface IdGeneratorInterface
{
    public function generate(): string;
}
