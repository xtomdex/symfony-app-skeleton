<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\DTO;

final class BrandConfig
{
    private string $logoLight = '';
    private ?string $logoDark = null;

    private function __construct(
        private readonly string $name,
    ) {}

    public static function create(string $name): self
    {
        return new self($name);
    }

    public function withLogos(string $light, ?string $dark = null): self
    {
        $clone = clone $this;
        $clone->logoLight = $light;
        $clone->logoDark = $dark;

        return $clone;
    }

    public function name(): string { return $this->name; }

    public function logoLight(): string { return $this->logoLight; }

    public function logoDark(): ?string { return $this->logoDark; }
}
