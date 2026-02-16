<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Controller\CommandBus;

use App\Domain\CommandBus\Contract\CommandInterface;
use App\Infrastructure\CommandBus\Trait\TwigCommandBusTrait;
use App\Tests\Fixtures\DTO\TestCommand;
use Symfony\Component\HttpFoundation\Request;

final class TestTwigRenderOnlyController extends TestBaseCommandBusController
{
    use TwigCommandBusTrait;

    public function __construct()
    {
        $this->template = '@test/command_bus_ok.html.twig';
        $this->formClassname = '';
    }

    protected function createCommand(Request $request): CommandInterface
    {
        return new TestCommand();
    }
}
