<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Infrastructure\Security\UserIdentity;
use App\Modules\User\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserIdentityTest extends TestCase
{
    public function test_from_user_maps_all_fields(): void
    {
        $user = User::create('uuid-1', 'john', 'hashed_pw');
        $identity = UserIdentity::fromUser($user);

        self::assertSame('uuid-1', $identity->getId());
        self::assertSame('john', $identity->getUsername());
        self::assertSame('john', $identity->getUserIdentifier());
        self::assertSame('hashed_pw', $identity->getPassword());
        self::assertSame(['ROLE_USER'], $identity->getRoles());
    }

    public function test_get_roles_returns_role_user_when_empty(): void
    {
        $identity = new UserIdentity('id', 'john', 'pw', []);

        self::assertSame(['ROLE_USER'], $identity->getRoles());
    }

    public function test_payload_defaults_to_empty_array(): void
    {
        $identity = new UserIdentity('id', 'john', 'pw');

        self::assertSame([], $identity->getPayload());
    }
}
