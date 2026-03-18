<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Unit\DTO;

use App\Modules\AdminPanel\DTO\AdminUserView;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(AdminUserView::class)]
final class AdminUserViewTest extends TestCase
{
    #[Test]
    public function constructor_sets_all_fields(): void
    {
        $view = new AdminUserView(
            displayName: 'John Doe',
            avatarUrl: 'https://example.com/avatar.jpg',
            role: 'Admin',
            profileUrl: '/admin/profile',
            logoutUrl: '/admin/logout',
        );

        self::assertSame('John Doe', $view->displayName);
        self::assertSame('https://example.com/avatar.jpg', $view->avatarUrl);
        self::assertSame('Admin', $view->role);
        self::assertSame('/admin/profile', $view->profileUrl);
        self::assertSame('/admin/logout', $view->logoutUrl);
    }

    #[Test]
    public function avatarUrl_can_be_null(): void
    {
        $view = new AdminUserView(
            displayName: 'Jane',
            avatarUrl: null,
            role: 'Editor',
            profileUrl: '/profile',
            logoutUrl: '/logout',
        );

        self::assertNull($view->avatarUrl);
    }
}
