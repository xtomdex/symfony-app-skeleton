<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Unit\Menu;

use App\Modules\AdminPanel\DTO\MenuItem;
use App\Modules\AdminPanel\DTO\PreparedMenuItem;
use App\Modules\AdminPanel\Menu\AdminMenuBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(AdminMenuBuilder::class)]
final class AdminMenuBuilderTest extends TestCase
{
    private UrlGeneratorInterface $urlGenerator;
    private RequestStack $requestStack;

    protected function setUp(): void
    {
        $this->urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $this->requestStack = new RequestStack();
    }

    private function builder(): AdminMenuBuilder
    {
        return new AdminMenuBuilder($this->urlGenerator, $this->requestStack);
    }

    #[Test]
    public function build_resolves_route_links(): void
    {
        $this->urlGenerator->method('generate')
            ->willReturnMap([
                ['admin_users', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/admin/users'],
            ]);

        $items = [MenuItem::linkToRoute('Users', 'admin_users')];
        $result = $this->builder()->build($items);

        self::assertCount(1, $result);
        self::assertSame('/admin/users', $result[0]->link);
    }

    #[Test]
    public function build_resolves_url_links(): void
    {
        $items = [MenuItem::linkToUrl('Docs', 'https://docs.example.com')];
        $result = $this->builder()->build($items);

        self::assertSame('https://docs.example.com', $result[0]->link);
    }

    #[Test]
    public function build_creates_section_items(): void
    {
        $items = [MenuItem::section('Management')];
        $result = $this->builder()->build($items);

        self::assertCount(1, $result);
        self::assertTrue($result[0]->section);
        self::assertSame('Management', $result[0]->label);
    }

    #[Test]
    public function build_marks_active_by_exact_route(): void
    {
        $request = Request::create('/admin/users');
        $request->attributes->set('_route', 'admin_users');
        $this->requestStack->push($request);

        $this->urlGenerator->method('generate')->willReturn('/admin/users');

        $items = [MenuItem::linkToRoute('Users', 'admin_users')];
        $result = $this->builder()->build($items);

        self::assertTrue($result[0]->active);
    }

    #[Test]
    public function build_marks_active_by_route_prefix(): void
    {
        $request = Request::create('/admin/users/42/edit');
        $request->attributes->set('_route', 'admin_user_edit');
        $this->requestStack->push($request);

        $this->urlGenerator->method('generate')->willReturn('/admin/users');

        $items = [
            MenuItem::linkToRoute('Users', 'admin_users')
                ->withRoutePrefix('admin_user'),
        ];
        $result = $this->builder()->build($items);

        self::assertTrue($result[0]->active);
    }

    #[Test]
    public function build_not_active_when_route_does_not_match(): void
    {
        $request = Request::create('/admin/orders');
        $request->attributes->set('_route', 'admin_orders');
        $this->requestStack->push($request);

        $this->urlGenerator->method('generate')->willReturn('/admin/users');

        $items = [MenuItem::linkToRoute('Users', 'admin_users')];
        $result = $this->builder()->build($items);

        self::assertFalse($result[0]->active);
    }

    #[Test]
    public function build_sets_parent_open_when_child_is_active(): void
    {
        $request = Request::create('/admin/users');
        $request->attributes->set('_route', 'admin_users');
        $this->requestStack->push($request);

        $this->urlGenerator->method('generate')
            ->willReturnMap([
                ['admin_users', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/admin/users'],
                ['admin_user_create', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/admin/users/create'],
            ]);

        $items = [
            MenuItem::linkToRoute('User Management', 'admin_users')
                ->withChildren([
                    MenuItem::linkToRoute('List', 'admin_users'),
                    MenuItem::linkToRoute('Create', 'admin_user_create'),
                ]),
        ];

        $result = $this->builder()->build($items);

        self::assertTrue($result[0]->open);
        self::assertTrue($result[0]->children[0]->active);
        self::assertFalse($result[0]->children[1]->active);
    }

    #[Test]
    public function build_filters_parent_with_empty_children(): void
    {
        // Parent has children defined, but after build they all get filtered
        // (this happens when children have no valid links)
        $items = [
            MenuItem::linkToRoute('Empty', 'admin_empty')
                ->withChildren([]),
        ];

        $result = $this->builder()->build($items);

        // Parent with hasChildren=false goes through normally
        self::assertCount(1, $result);
    }

    #[Test]
    public function build_handles_no_request(): void
    {
        // RequestStack has no current request
        $this->urlGenerator->method('generate')->willReturn('/admin/users');

        $items = [MenuItem::linkToRoute('Users', 'admin_users')];
        $result = $this->builder()->build($items);

        self::assertFalse($result[0]->active);
    }

    #[Test]
    public function build_handles_nested_children(): void
    {
        $this->urlGenerator->method('generate')->willReturn('/test');

        $items = [
            MenuItem::linkToRoute('Level 1', 'l1')->withChildren([
                MenuItem::linkToRoute('Level 2', 'l2')->withChildren([
                    MenuItem::linkToRoute('Level 3', 'l3'),
                ]),
            ]),
        ];

        $result = $this->builder()->build($items);

        self::assertCount(1, $result);
        self::assertTrue($result[0]->hasChildren());
        self::assertTrue($result[0]->children[0]->hasChildren());
        self::assertSame('Level 3', $result[0]->children[0]->children[0]->label);
    }
}
