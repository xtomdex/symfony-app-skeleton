<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Unit\DTO;

use App\Modules\AdminPanel\DTO\DataColumn;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DataColumn::class)]
final class DataColumnTest extends TestCase
{
    #[Test]
    public function creation_with_all_params(): void
    {
        $column = new DataColumn(
            label: 'Name',
            property: 'name',
            template: 'components/cell.html.twig',
            sortable: true,
            sortField: 'user_name',
        );

        self::assertSame('Name', $column->label);
        self::assertSame('name', $column->property);
        self::assertSame('components/cell.html.twig', $column->template);
        self::assertTrue($column->sortable);
        self::assertSame('user_name', $column->sortField);
    }

    #[Test]
    public function defaults_are_null_and_false(): void
    {
        $column = new DataColumn(label: 'Email');

        self::assertSame('Email', $column->label);
        self::assertNull($column->property);
        self::assertNull($column->template);
        self::assertFalse($column->sortable);
        self::assertNull($column->sortField);
    }

    #[Test]
    public function creation_with_label_only(): void
    {
        $column = new DataColumn('Status');

        self::assertSame('Status', $column->label);
        self::assertNull($column->property);
        self::assertNull($column->template);
        self::assertFalse($column->sortable);
        self::assertNull($column->sortField);
    }
}
