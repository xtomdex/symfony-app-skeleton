<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Service\Eventing;

use App\Tests\Fixtures\DTO\TestEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final readonly class TestEventListener
{
    public function __construct(
        private EventCounter $counter,
    ) {}

    public function __invoke(TestEvent $event): void
    {
        $this->counter->increment();
    }
}
