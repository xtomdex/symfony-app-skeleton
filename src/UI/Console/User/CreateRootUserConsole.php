<?php

declare(strict_types=1);

namespace App\UI\Console\User;

use App\Infrastructure\Security\UserIdentity;
use App\Modules\User\UseCase\Create\Root\CreateRootUserCommand;
use App\Modules\User\UseCase\Create\Root\CreateRootUserHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'users:create-root',
    description: 'Create the root user',
)]
final class CreateRootUserConsole extends Command
{
    public function __construct(
        private readonly CreateRootUserHandler $handler,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = $io->ask('Username (email)');
        $password = $io->askHidden('Password');

        $hashedPassword = $this->passwordHasher->hashPassword(UserIdentity::forHasher(), $password);

        try {
            $user = ($this->handler)(new CreateRootUserCommand($username, $hashedPassword));
            $io->success(sprintf('Root user created: %s (%s)', $user->getUsername(), $user->getId()));

            return Command::SUCCESS;
        } catch (\DomainException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
