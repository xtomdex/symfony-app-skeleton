<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\DTO;

final readonly class Breadcrumb
{
    public function __construct(
        public string $label,
        public ?string $url = null,
        public ?string $icon = null,
    ) {}
}
