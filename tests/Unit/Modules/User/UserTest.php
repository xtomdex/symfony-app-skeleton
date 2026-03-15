<?php

declare(strict_types=1);

namespace App\Tests\Unit\Modules\User;

use App\Modules\User\Entity\User;
use App\Modules\User\Enum\UserRole;
use App\Modules\User\Enum\UserStatus;
use App\Modules\User\Enum\UserType;
use App\Modules\User\Event\UserCreatedEvent;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function test_create_returns_user_instance(): void
    {
        $user = User::create('uuid-1', 'john', 'hashed_pw');

        self::assertInstanceOf(User::class, $user);
    }

    public function test_create_sets_all_fields(): void
    {
        $user = User::create('uuid-1', 'john', 'hashed_pw');

        self::assertSame('uuid-1', $user->getId());
        self::assertSame('john', $user->getUsername());
        self::assertSame('hashed_pw', $user->getPassword());
        self::assertSame([UserRole::USER->value], $user->getRoles());
        self::assertSame(UserStatus::ACTIVE, $user->getStatus());
        self::assertSame(UserType::CLIENT, $user->getType());
    }

    public function test_create_records_user_created_event(): void
    {
        $user = User::create('uuid-1', 'john', 'hashed_pw');

        $events = $user->releaseEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(UserCreatedEvent::class, $events[0]);
        self::assertSame('uuid-1', $events[0]->id);
        self::assertSame('john', $events[0]->username);
    }
}
