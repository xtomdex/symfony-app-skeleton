<?php

declare(strict_types=1);

namespace App\Domain\Eventing\Contract;

interface PublicEventPublisherInterface
{
    public function publish(object $event): void;
}
