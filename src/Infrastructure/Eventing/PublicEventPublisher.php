<?php

declare(strict_types=1);

namespace App\Infrastructure\Eventing;

use App\Domain\Eventing\Contract\PublicEventPublisherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class PublicEventPublisher implements PublicEventPublisherInterface
{
    public function __construct(
        private EventDispatcherInterface $symfonyDispatcher
    ) {}
    public function publish(object $event): void
    {
        $this->symfonyDispatcher->dispatch($event);
    }
}
