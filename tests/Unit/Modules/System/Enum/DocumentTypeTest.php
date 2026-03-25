<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\Enum;

use App\Modules\System\Enum\DocumentType;
use PHPUnit\Framework\TestCase;

final class DocumentTypeTest extends TestCase
{
    public function test_legal_has_correct_value(): void
    {
        self::assertSame('legal', DocumentType::LEGAL->value);
    }

    public function test_page_has_correct_value(): void
    {
        self::assertSame('page', DocumentType::PAGE->value);
    }

    public function test_from_legal(): void
    {
        self::assertSame(DocumentType::LEGAL, DocumentType::from('legal'));
    }

    public function test_from_page(): void
    {
        self::assertSame(DocumentType::PAGE, DocumentType::from('page'));
    }
}
