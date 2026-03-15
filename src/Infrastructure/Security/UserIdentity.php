<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Modules\User\Entity\User;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class UserIdentity implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        private string $id,
        private string $username,
        private string $password,
        private array $roles = ['ROLE_USER'],
        private array $payload = []
    ) {}

    public static function fromUser(User $user): self
    {
        return new self(
            $user->getId(),
            $user->getUsername(),
            $user->getPassword(),
            $user->getRoles()
        );
    }

    public static function forHasher(): self
    {
        return new self('id', 'username', 'password');
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return !empty($this->roles) ? $this->roles : ['ROLE_USER'];
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
