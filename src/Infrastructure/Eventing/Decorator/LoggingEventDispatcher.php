<?php

declare(strict_types=1);

namespace App\Infrastructure\Eventing\Decorator;

use App\Domain\Eventing\Contract\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final readonly class LoggingEventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private EventDispatcherInterface $inner,
        private LoggerInterface $logger,
    ) {}

    public function dispatch(iterable $events): void
    {
        $events = is_array($events) ? $events : iterator_to_array($events, false);

        foreach ($events as $event) {
            try {
                $this->logger->info('Domain event dispatched', [
                    'event_class' => $event::class,
                    'event_id'    => Uuid::uuid4()->toString(),
                    'payload'     => $event->getPayload(),
                ]);
            } catch (\Throwable $e) {
                // Logging must NEVER break event dispatching.
                // Fallback to generic error log.
                $this->logger->error('Failed to log domain event', [
                    'exception'   => $e->getMessage(),
                    'event_class' => get_class($event),
                ]);
            }
        }

        $this->inner->dispatch($events);
    }
}
