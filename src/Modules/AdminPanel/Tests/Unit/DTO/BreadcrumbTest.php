<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Unit\DTO;

use App\Modules\AdminPanel\DTO\Breadcrumb;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Breadcrumb::class)]
final class BreadcrumbTest extends TestCase
{
    #[Test]
    public function creation_with_all_params(): void
    {
        $breadcrumb = new Breadcrumb(label: 'Users', url: '/admin/users', icon: 'ti-users');

        self::assertSame('Users', $breadcrumb->label);
        self::assertSame('/admin/users', $breadcrumb->url);
        self::assertSame('ti-users', $breadcrumb->icon);
    }

    #[Test]
    public function default_values_are_null(): void
    {
        $breadcrumb = new Breadcrumb(label: 'Home');

        self::assertSame('Home', $breadcrumb->label);
        self::assertNull($breadcrumb->url);
        self::assertNull($breadcrumb->icon);
    }

    #[Test]
    public function creation_with_label_only(): void
    {
        $breadcrumb = new Breadcrumb('Dashboard');

        self::assertSame('Dashboard', $breadcrumb->label);
        self::assertNull($breadcrumb->url);
        self::assertNull($breadcrumb->icon);
    }
}
