<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Controller\CommandBus;

use App\Domain\CommandBus\Contract\CommandInterface;
use App\Infrastructure\CommandBus\Trait\TwigCommandBusTrait;
use App\Tests\Fixtures\DTO\TestCommand;
use App\Tests\Fixtures\Service\CommandBus\Form\TestCommandFormType;
use App\Tests\Fixtures\Service\CommandBus\Handler\TestFailingHandler;
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
