<?php

declare(strict_types=1);

namespace App\Modules\User\UseCase\Create\Root;

use App\Domain\Eventing\Contract\FlusherInterface;
use App\Modules\User\Entity\User;
use App\Modules\User\Enum\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

final readonly class CreateRootUserHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FlusherInterface $flusher
    ) {}

    public function __invoke(CreateRootUserCommand $cmd): User
    {
        $existing = $this->entityManager->getRepository(User::class)->findOneBy([
            'type' => UserType::ROOT,
        ]);

        if ($existing) {
            throw new \DomainException('Root user already exists.');
        }

        $user = User::createRoot(Uuid::uuid4()->toString(), $cmd->username, $cmd->password);

        $this->entityManager->persist($user);

        $this->flusher->flush($user);

        return $user;
    }
}
