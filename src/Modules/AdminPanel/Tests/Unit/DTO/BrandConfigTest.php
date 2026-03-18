<?php

declare(strict_types=1);

namespace App\Modules\AdminPanel\Tests\Unit\DTO;

use App\Modules\AdminPanel\DTO\BrandConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BrandConfig::class)]
final class BrandConfigTest extends TestCase
{
    #[Test]
    public function create_sets_name_with_empty_logos(): void
    {
        $brand = BrandConfig::create('My App');

        self::assertSame('My App', $brand->name());
        self::assertSame('', $brand->logoLight());
        self::assertNull($brand->logoDark());
    }

    #[Test]
    public function withLogos_returns_new_instance(): void
    {
        $original = BrandConfig::create('My App');
        $withLogos = $original->withLogos('img/logo-light.svg', 'img/logo-dark.svg');

        self::assertSame('', $original->logoLight());
        self::assertSame('img/logo-light.svg', $withLogos->logoLight());
        self::assertSame('img/logo-dark.svg', $withLogos->logoDark());
        self::assertNotSame($original, $withLogos);
    }

    #[Test]
    public function withLogos_accepts_light_only(): void
    {
        $brand = BrandConfig::create('My App')->withLogos('img/logo.svg');

        self::assertSame('img/logo.svg', $brand->logoLight());
        self::assertNull($brand->logoDark());
    }
}
