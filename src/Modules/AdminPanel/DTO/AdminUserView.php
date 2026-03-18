<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\DTO;

final readonly class AdminUserView
{
    public function __construct(
        public string  $displayName,
        public ?string $avatarUrl,
        public string  $role,
        public string  $profileUrl,
        public string  $logoutUrl,
    ) {}
}
