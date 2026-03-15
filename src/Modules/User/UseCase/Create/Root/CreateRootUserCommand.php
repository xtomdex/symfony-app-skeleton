<?php

declare(strict_types=1);

namespace App\Modules\User\UseCase\Create\Root;

use App\Domain\CommandBus\Contract\CommandInterface;

final readonly class CreateRootUserCommand implements CommandInterface
{
    public function __construct(
        public string $username,
        public string $password
    ) {}
}
