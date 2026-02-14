<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Controller;

use App\Domain\Contract\CommandInterface;
use App\Infrastructure\CommandBus\Trait\TwigCommandBusTrait;
use App\Tests\Fixtures\Command\TestCommand;
use App\Tests\Fixtures\Form\TestCommandFormType;
use App\Tests\Fixtures\Handler\TestFailingHandler;
use Symfony\Component\HttpFoundation\Request;

final class TestTwigInvalidFormController extends TestBaseCommandBusController
{
    use TwigCommandBusTrait;

    public function __construct(private readonly TestFailingHandler $handler)
    {
        $this->template = '@test/command_bus_form.html.twig';
        $this->formClassname = TestCommandFormType::class;
    }

    protected function createCommand(Request $request): CommandInterface
    {
        return new TestCommand();
    }
}
