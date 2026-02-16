<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Controller\CommandBus;

use App\Domain\CommandBus\Contract\CommandInterface;
use App\Infrastructure\CommandBus\Trait\ApiCommandBusTrait;
use App\Tests\Fixtures\DTO\TestCommand;
use App\Tests\Fixtures\Service\CommandBus\Form\TestCommandFormType;
use App\Tests\Fixtures\Service\CommandBus\Handler\TestSuccessHandler;
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
