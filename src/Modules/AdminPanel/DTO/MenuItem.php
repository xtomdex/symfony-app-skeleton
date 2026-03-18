<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\DTO;

final class MenuItem
{
    private ?string $icon = null;
    private ?string $routePrefix = null;
    /** @var list<string> */
    private array $rolesAny = [];
    /** @var list<MenuItem> */
    private array $children = [];
    private bool $isSection = false;

    private function __construct(
        private readonly string  $label,
        private readonly ?string $route,
        private readonly array   $routeParams,
        private readonly ?string $url,
    ) {}

    public static function linkToRoute(string $label, string $route, array $routeParams = []): self
    {
        return new self(
            label: $label,
            route: $route,
            routeParams: $routeParams,
            url: null,
        );
    }

    public static function linkToUrl(string $label, string $url): self
    {
        return new self(
            label: $label,
            route: null,
            routeParams: [],
            url: $url,
        );
    }

    public static function section(string $label): self
    {
        $self = new self(
            label: $label,
            route: null,
            routeParams: [],
            url: null,
        );
        $self->isSection = true;

        return $self;
    }

    public function withIcon(string $icon): self
    {
        $clone = clone $this;
        $clone->icon = $icon;

        return $clone;
    }

    /**
     * Mark parent active when current route starts with prefix.
     */
    public function withRoutePrefix(string $routePrefix): self
    {
        $clone = clone $this;
        $clone->routePrefix = $routePrefix;

        return $clone;
    }

    /**
     * Visible if user has ANY of these roles.
     *
     * @param list<string> $rolesAny
     */
    public function visibleForRoles(array $rolesAny): self
    {
        $clone = clone $this;
        $clone->rolesAny = $rolesAny;

        return $clone;
    }

    public function addChild(self $child): self
    {
        $clone = clone $this;
        $clone->children[] = $child;

        return $clone;
    }

    /**
     * @param list<MenuItem> $children
     */
    public function withChildren(array $children): self
    {
        $clone = clone $this;
        $clone->children = $children;

        return $clone;
    }

    public function label(): string { return $this->label; }

    public function icon(): ?string { return $this->icon; }

    public function route(): ?string { return $this->route; }

    public function routeParams(): array { return $this->routeParams; }

    public function routePrefix(): ?string { return $this->routePrefix; }

    public function url(): ?string { return $this->url; }

    public function isSection(): bool { return $this->isSection; }

    /** @return list<string> */
    public function rolesAny(): array { return $this->rolesAny; }

    /** @return list<MenuItem> */
    public function children(): array { return $this->children; }

    public function hasChildren(): bool
    {
        return count($this->children) > 0;
    }
}
