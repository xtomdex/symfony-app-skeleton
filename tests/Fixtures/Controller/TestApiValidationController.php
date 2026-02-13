<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Controller;

use App\Domain\Contract\CommandInterface;
use App\Infrastructure\CommandBus\Trait\ApiCommandBusTrait;
use App\Tests\Fixtures\Command\TestCommand;
use App\Tests\Fixtures\Form\TestCommandFormType;
use App\Tests\Fixtures\Handler\TestSuccessHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TestApiValidationController extends TestBaseCommandBusController
{
    use ApiCommandBusTrait;

    public function __construct(private readonly TestSuccessHandler $handler)
    {
        $this->responseCode = Response::HTTP_OK;
        $this->formClassname = TestCommandFormType::class;
    }

    protected function createCommand(Request $request): CommandInterface
    {
        return new TestCommand();
    }
}
