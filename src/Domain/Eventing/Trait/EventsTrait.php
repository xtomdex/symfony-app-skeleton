<?php

declare(strict_types=1);

namespace App\Domain\Eventing\Trait;

use App\Domain\Eventing\Contract\DomainEventInterface;

trait EventsTrait
{
    /** @var DomainEventInterface[] */
    private array $recordedEvents = [];

    protected function recordEvent(DomainEventInterface $event): void
    {
        $this->recordedEvents[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }
}
