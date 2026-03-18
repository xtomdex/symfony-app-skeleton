<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Unit\DTO;

use App\Modules\AdminPanel\DTO\PreparedMenuItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PreparedMenuItem::class)]
final class PreparedMenuItemTest extends TestCase
{
    #[Test]
    public function constructor_sets_all_fields(): void
    {
        $child = new PreparedMenuItem('Child', null, '/child', false, false, []);
        $item = new PreparedMenuItem('Parent', 'tabler-users', '/parent', true, true, [$child]);

        self::assertSame('Parent', $item->label);
        self::assertSame('tabler-users', $item->icon);
        self::assertSame('/parent', $item->link);
        self::assertTrue($item->active);
        self::assertTrue($item->open);
        self::assertCount(1, $item->children);
        self::assertFalse($item->section);
    }

    #[Test]
    public function section_creates_section_item(): void
    {
        $item = PreparedMenuItem::section('Management');

        self::assertSame('Management', $item->label);
        self::assertTrue($item->section);
        self::assertNull($item->icon);
        self::assertNull($item->link);
        self::assertFalse($item->active);
        self::assertFalse($item->open);
        self::assertSame([], $item->children);
    }

    #[Test]
    public function hasChildren_returns_true_when_children_exist(): void
    {
        $child = new PreparedMenuItem('Child', null, '/child', false, false, []);
        $item = new PreparedMenuItem('Parent', null, null, false, false, [$child]);

        self::assertTrue($item->hasChildren());
    }

    #[Test]
    public function hasChildren_returns_false_when_no_children(): void
    {
        $item = new PreparedMenuItem('Leaf', null, '/leaf', false, false, []);

        self::assertFalse($item->hasChildren());
    }
}
