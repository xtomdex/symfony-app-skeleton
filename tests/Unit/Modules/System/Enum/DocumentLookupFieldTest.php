<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\Enum;

use App\Modules\System\Enum\DocumentLookupField;
use PHPUnit\Framework\TestCase;

final class DocumentLookupFieldTest extends TestCase
{
    public function test_id_has_correct_value(): void
    {
        self::assertSame('id', DocumentLookupField::Id->value);
    }

    public function test_slug_has_correct_value(): void
    {
        self::assertSame('slug', DocumentLookupField::Slug->value);
    }

    public function test_from_id(): void
    {
        self::assertSame(DocumentLookupField::Id, DocumentLookupField::from('id'));
    }

    public function test_from_slug(): void
    {
        self::assertSame(DocumentLookupField::Slug, DocumentLookupField::from('slug'));
    }
}
