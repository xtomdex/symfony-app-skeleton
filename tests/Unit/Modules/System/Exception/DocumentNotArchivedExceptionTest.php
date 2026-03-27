<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\System\Exception;

use App\Modules\System\Exception\DocumentNotArchivedException;
use PHPUnit\Framework\TestCase;

final class DocumentNotArchivedExceptionTest extends TestCase
{
    public function test_for_document(): void
    {
        $exception = DocumentNotArchivedException::forDocument('abc-123');

        self::assertStringContainsString('abc-123', $exception->getMessage());
    }
}
