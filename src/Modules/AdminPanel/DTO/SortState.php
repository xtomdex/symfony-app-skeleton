<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\DTO;

final readonly class SortState
{
    public function __construct(
        public string $field,
        public string $direction // 'asc' or 'desc'
    ) {}
}
