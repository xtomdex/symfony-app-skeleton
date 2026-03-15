<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\User\UseCase;

use App\Domain\Eventing\Contract\FlusherInterface;
use App\Modules\User\Entity\User;
use App\Modules\User\Enum\UserRole;
use App\Modules\User\Enum\UserStatus;
use App\Modules\User\Enum\UserType;
use App\Modules\User\UseCase\Create\Root\CreateRootUserCommand;
use App\Modules\User\UseCase\Create\Root\CreateRootUserHandler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

final class CreateRootUserHandlerTest extends TestCase
{
    public function test_creates_root_user_when_none_exists(): void
    {
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);

        $flusher = $this->createStub(FlusherInterface::class);

        $handler = new CreateRootUserHandler($em, $flusher);
        $command = new CreateRootUserCommand('admin@example.com', 'hashed_pw');

        $user = ($handler)($command);

        self::assertInstanceOf(User::class, $user);
        self::assertSame('admin@example.com', $user->getUsername());
        self::assertSame('hashed_pw', $user->getPassword());
        self::assertSame(UserType::ROOT, $user->getType());
        self::assertSame(UserStatus::ACTIVE, $user->getStatus());
        self::assertSame([UserRole::USER->value, UserRole::SUPER_ADMIN->value], $user->getRoles());
    }

    public function test_throws_when_root_already_exists(): void
    {
        $existingRoot = User::createRoot('uuid-1', 'existing@example.com', 'pw');

        $repository = $this->createStub(EntityRepository::class);
        $repository->method('findOneBy')->willReturn($existingRoot);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);

        $flusher = $this->createStub(FlusherInterface::class);

        $handler = new CreateRootUserHandler($em, $flusher);
        $command = new CreateRootUserCommand('new@example.com', 'hashed_pw');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Root user already exists.');

        ($handler)($command);
    }
}
