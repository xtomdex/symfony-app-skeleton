<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Unit\DTO;

use App\Modules\AdminPanel\DTO\SortState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SortState::class)]
final class SortStateTest extends TestCase
{
    #[Test]
    public function creation_and_field_access(): void
    {
        $state = new SortState(field: 'email', direction: 'desc');

        self::assertSame('email', $state->field);
        self::assertSame('desc', $state->direction);
    }

    #[Test]
    public function creation_with_asc_direction(): void
    {
        $state = new SortState('name', 'asc');

        self::assertSame('name', $state->field);
        self::assertSame('asc', $state->direction);
    }
}
