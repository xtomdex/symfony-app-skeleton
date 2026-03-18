<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\DTO;

final class FooterConfig
{
    private ?string $url = null;

    private function __construct(
        private readonly string $text,
    ) {}

    public static function create(string $text): self
    {
        return new self($text);
    }

    public function withUrl(string $url): self
    {
        $clone = clone $this;
        $clone->url = $url;

        return $clone;
    }

    public function text(): string { return $this->text; }

    public function url(): ?string { return $this->url; }
}
