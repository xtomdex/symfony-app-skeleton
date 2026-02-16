<?php

declare(strict_types=1);

namespace App\Tests\Functional\Eventing;

use App\Tests\Fixtures\DTO\TestEvent;
use App\Tests\Fixtures\Service\Eventing\EventCounter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class EventingSmokeTest extends KernelTestCase
{
    public function test_symfony_dispatcher_invokes_autoconfigured_listener_by_typehint(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        $dispatcher = $container->get(EventDispatcherInterface::class);
        \assert($dispatcher instanceof EventDispatcherInterface);

        $counter = $container->get(EventCounter::class);
        \assert($counter instanceof EventCounter);

        self::assertSame(0, $counter->getCount());

        $dispatcher->dispatch(new TestEvent('e1'));

        self::assertSame(1, $counter->getCount());
    }
}
