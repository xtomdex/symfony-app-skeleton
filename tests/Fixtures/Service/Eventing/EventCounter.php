<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Service\Eventing;

final class EventCounter
{
    private int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
