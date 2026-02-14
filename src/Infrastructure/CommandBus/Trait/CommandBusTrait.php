<?php

declare(strict_types=1);

namespace App\Infrastructure\CommandBus\Trait;

use App\Domain\Contract\CommandBusExceptionMapperInterface;
use App\Domain\Contract\CommandInterface;
use App\Domain\DTO\CommandBusError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait CommandBusTrait
{
    protected string $formClassname = '';

    public function __invoke(Request $request): Response
    {
        try {
            $command = $this->createCommand($request);
            $this->handleForm($command, $request);

            $this->beforeHandleCommand($command, $request);
            $result = $this->handleCommand($command);
            $processedResult = $this->afterHandleCommand($result, $request);

            return $this->contentResponse($processedResult);
        } catch (\Throwable $e) {
            $error = $this->exceptionMapper()->map($e);
            return $this->errorResponse($error);
        }
    }

    // Lifecycle steps with default behavior
    protected function beforeHandleCommand(CommandInterface $command, Request $request): void {}
    protected function handleCommand(CommandInterface $command): mixed
    {
        if (!isset($this->handler)) {
            return null;
        }

        return ($this->handler)($command);
    }
    protected function afterHandleCommand(mixed $result, Request $request): mixed { return $result; }

    // Required to be implemented in final command bus controller
    abstract protected function createCommand(Request $request): CommandInterface;

    // Implemented in Twig/Api trait
    abstract protected function handleForm(CommandInterface $command, Request $request): void;
    abstract protected function contentResponse(mixed $result): Response;
    abstract protected function errorResponse(CommandBusError $error): Response;

    // Implemented in custom command bus abstract controller (via service container)
    abstract protected function exceptionMapper(): CommandBusExceptionMapperInterface;

    // Implemented in Symfony abstract controller
    abstract protected function createForm(string $type, mixed $data = null, array $options = []): FormInterface;
}
