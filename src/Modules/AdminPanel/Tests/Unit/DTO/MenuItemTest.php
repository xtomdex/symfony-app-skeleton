<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Unit\DTO;

use App\Modules\AdminPanel\DTO\MenuItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MenuItem::class)]
final class MenuItemTest extends TestCase
{
    #[Test]
    public function linkToRoute_creates_item_with_route(): void
    {
        $item = MenuItem::linkToRoute('Users', 'admin_users', ['page' => 1]);

        self::assertSame('Users', $item->label());
        self::assertSame('admin_users', $item->route());
        self::assertSame(['page' => 1], $item->routeParams());
        self::assertNull($item->url());
        self::assertNull($item->icon());
        self::assertNull($item->routePrefix());
        self::assertFalse($item->isSection());
        self::assertFalse($item->hasChildren());
        self::assertSame([], $item->rolesAny());
    }

    #[Test]
    public function linkToUrl_creates_item_with_url(): void
    {
        $item = MenuItem::linkToUrl('Docs', 'https://docs.example.com');

        self::assertSame('Docs', $item->label());
        self::assertNull($item->route());
        self::assertSame('https://docs.example.com', $item->url());
    }

    #[Test]
    public function section_creates_section_item(): void
    {
        $item = MenuItem::section('Management');

        self::assertSame('Management', $item->label());
        self::assertTrue($item->isSection());
        self::assertNull($item->route());
        self::assertNull($item->url());
    }

    #[Test]
    public function withIcon_returns_new_instance(): void
    {
        $original = MenuItem::linkToRoute('Users', 'admin_users');
        $withIcon = $original->withIcon('tabler-users');

        self::assertNull($original->icon());
        self::assertSame('tabler-users', $withIcon->icon());
        self::assertNotSame($original, $withIcon);
    }

    #[Test]
    public function withRoutePrefix_returns_new_instance(): void
    {
        $original = MenuItem::linkToRoute('Users', 'admin_users');
        $withPrefix = $original->withRoutePrefix('admin_user');

        self::assertNull($original->routePrefix());
        self::assertSame('admin_user', $withPrefix->routePrefix());
        self::assertNotSame($original, $withPrefix);
    }

    #[Test]
    public function visibleForRoles_returns_new_instance(): void
    {
        $original = MenuItem::linkToRoute('Users', 'admin_users');
        $withRoles = $original->visibleForRoles(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);

        self::assertSame([], $original->rolesAny());
        self::assertSame(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'], $withRoles->rolesAny());
    }

    #[Test]
    public function withChildren_returns_new_instance_with_children(): void
    {
        $child1 = MenuItem::linkToRoute('List', 'admin_users');
        $child2 = MenuItem::linkToRoute('Create', 'admin_user_create');
        $parent = MenuItem::linkToRoute('Users', 'admin_users')
            ->withChildren([$child1, $child2]);

        self::assertTrue($parent->hasChildren());
        self::assertCount(2, $parent->children());
    }

    #[Test]
    public function addChild_appends_to_children(): void
    {
        $child = MenuItem::linkToRoute('List', 'admin_users');
        $parent = MenuItem::linkToRoute('Users', 'admin_users');

        $withOne = $parent->addChild($child);
        $withTwo = $withOne->addChild($child);

        self::assertFalse($parent->hasChildren());
        self::assertCount(1, $withOne->children());
        self::assertCount(2, $withTwo->children());
    }

    #[Test]
    public function fluent_api_chains_correctly(): void
    {
        $item = MenuItem::linkToRoute('Users', 'admin_users')
            ->withIcon('tabler-users')
            ->withRoutePrefix('admin_user')
            ->visibleForRoles(['ROLE_ADMIN'])
            ->withChildren([
                MenuItem::linkToRoute('List', 'admin_users'),
            ]);

        self::assertSame('tabler-users', $item->icon());
        self::assertSame('admin_user', $item->routePrefix());
        self::assertSame(['ROLE_ADMIN'], $item->rolesAny());
        self::assertTrue($item->hasChildren());
    }
}
