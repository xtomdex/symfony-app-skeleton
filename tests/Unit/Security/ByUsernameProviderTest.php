<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Infrastructure\Security\Provider\ByUsernameProvider;
use App\Infrastructure\Security\UserIdentity;
use App\Modules\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

final class ByUsernameProviderTest extends TestCase
{
    public function test_load_returns_user_identity(): void
    {
        $user = User::create('uuid-1', 'john', 'hashed_pw');
        $provider = $this->createProvider($user);

        $identity = $provider->loadUserByIdentifier('john');

        self::assertInstanceOf(UserIdentity::class, $identity);
        self::assertSame('john', $identity->getUserIdentifier());
    }

    public function test_load_throws_when_user_not_found(): void
    {
        $provider = $this->createProvider(null);

        $this->expectException(UserNotFoundException::class);
        $provider->loadUserByIdentifier('unknown');
    }

    public function test_refresh_delegates_to_load(): void
    {
        $user = User::create('uuid-1', 'john', 'hashed_pw');
        $provider = $this->createProvider($user);

        $original = UserIdentity::fromUser($user);
        $refreshed = $provider->refreshUser($original);

        self::assertSame('john', $refreshed->getUserIdentifier());
    }

    public function test_refresh_throws_on_invalid_user_class(): void
    {
        $provider = $this->createProvider(null);

        $foreignUser = $this->createStub(UserInterface::class);

        $this->expectException(\InvalidArgumentException::class);
        $provider->refreshUser($foreignUser);
    }

    public function test_supports_user_identity_class(): void
    {
        $provider = $this->createProvider(null);

        self::assertTrue($provider->supportsClass(UserIdentity::class));
        self::assertFalse($provider->supportsClass(\stdClass::class));
    }

    private function createProvider(?User $user): ByUsernameProvider
    {
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('findOneBy')->willReturn($user);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')
            ->with(User::class)
            ->willReturn($repository);

        return new ByUsernameProvider($em);
    }
}
