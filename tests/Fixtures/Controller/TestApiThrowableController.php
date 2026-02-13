<?php

declare(strict_types=1);

namespace App\Tests\Fixtures\Controller;

use App\Domain\Contract\CommandInterface;
use App\Infrastructure\CommandBus\Trait\ApiCommandBusTrait;
use App\Tests\Fixtures\Command\TestCommand;
use App\Tests\Fixtures\Handler\TestThrowableHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TestApiThrowableController extends TestBaseCommandBusController
{
    use ApiCommandBusTrait;

    public function __construct(private readonly TestThrowableHandler $handler)
    {
        $this->responseCode = Response::HTTP_OK;
        $this->formClassname = '';
    }

    protected function createCommand(Request $request): CommandInterface
    {
        return new TestCommand('throw an error');
    }
}
