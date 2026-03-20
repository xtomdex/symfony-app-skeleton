<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\DTO;

final readonly class DataColumn
{
    public function __construct(
        public string $label,
        public ?string $property = null,
        public ?string $template = null,
        public bool $sortable = false,
        public ?string $sortField = null,
    ) {}
}
