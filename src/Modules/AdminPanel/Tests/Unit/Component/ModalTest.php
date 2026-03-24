<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Unit\Component;

use App\Modules\AdminPanel\Twig\Component\Modal;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Modal::class)]
final class ModalTest extends TestCase
{
    #[Test]
    public function default_property_values(): void
    {
        $modal = new Modal();
        $modal->id = 'testModal';

        self::assertSame('testModal', $modal->id);
        self::assertSame('', $modal->title);
        self::assertNull($modal->size);
        self::assertFalse($modal->scrollable);
        self::assertFalse($modal->staticBackdrop);
        self::assertFalse($modal->centered);
        self::assertTrue($modal->closable);
    }
}
