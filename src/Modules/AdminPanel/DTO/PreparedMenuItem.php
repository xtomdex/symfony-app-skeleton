<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\DTO;

final readonly class PreparedMenuItem
{
    /**
     * @param list<PreparedMenuItem> $children
     */
    public function __construct(
        public string  $label,
        public ?string $icon,
        public ?string $link,
        public bool    $active,
        public bool    $open,
        public array   $children,
        public bool    $section = false,
    ) {}

    public static function section(string $label): self
    {
        return new self($label, null, null, false, false, [], true);
    }

    public function hasChildren(): bool
    {
        return count($this->children) > 0;
    }
}
