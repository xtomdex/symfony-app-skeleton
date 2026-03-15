<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Provider;

use App\Infrastructure\Security\UserIdentity;
use App\Modules\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

readonly class ByUsernameProvider implements UserProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof UserIdentity) {
            throw new \InvalidArgumentException('Invalid user object.');
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return $class === UserIdentity::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $repository = $this->entityManager->getRepository(User::class);
        $user = $repository->findOneBy(['username' => $identifier]);

        if (!$user) {
            throw new UserNotFoundException('User not found.');
        }

        return UserIdentity::fromUser($user);
    }
}
