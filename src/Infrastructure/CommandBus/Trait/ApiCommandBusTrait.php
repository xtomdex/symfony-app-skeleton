<?php

declare(strict_types=1);

namespace App\Infrastructure\CommandBus\Trait;

use App\Domain\Contract\CommandInterface;
use App\Domain\DTO\ApiResponse;
use App\Domain\DTO\CommandBusError;
use App\Domain\Exception\ValidationException;
use App\Infrastructure\Form\FormErrorNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait ApiCommandBusTrait
{
    use CommandBusTrait;

    protected int $responseCode = Response::HTTP_OK;

    // Ensure that API controller has a handler injected.
    // Override this method including its parent or do not forget to check handler existence.
    protected function beforeHandleCommand(CommandInterface $command, Request $request): void
    {
        if (!isset($this->handler)) {
            throw new \LogicException('Handler is required for API controllers.');
        }
    }

    protected function handleForm(CommandInterface $command, Request $request): void
    {
        if (!$this->formClassname) {
            return;
        }

        $form = $this->createForm($this->formClassname, $command, ['csrf_protection' => false]);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            throw new ValidationException('Request payload is empty or invalid.', []);
        }

        if (!$form->isValid()) {
            $normalized = $this->formErrorNormalizer()->normalize($form);
            throw new ValidationException($normalized['message'], $normalized['errors']);
        }
    }

    protected function contentResponse(mixed $result): Response
    {
        $code = match ($this->responseCode) {
            Response::HTTP_CREATED => Response::HTTP_CREATED,
            default => Response::HTTP_OK,
        };

        $result = ($this->responseCode === Response::HTTP_NO_CONTENT) ? null : $result;

        $payload = ApiResponse::success($result, $code);

        return $this->json($payload->toArray(), $code);
    }

    protected function errorResponse(CommandBusError $error): Response
    {
        $result = $error->redirect ? [
            'route' => $error->redirect->route,
            'params' => $error->redirect->params,
        ] : null;

        $errors = $error->statusCode === Response::HTTP_BAD_REQUEST ? ($error->errors ?: null) : null;

        $payload = ApiResponse::failure(
            errorCode: $error->errorCode,
            errorMessage: $error->message,
            statusCode: $error->statusCode,
            errors: $errors,
            result: $result
        );

        return $this->json($payload->toArray(), $error->statusCode);
    }

    // Implemented in custom command bus abstract controller
    abstract protected function formErrorNormalizer(): FormErrorNormalizer;

    // Implemented in Symfony abstract controller
    abstract protected function json(mixed $data, int $status = 200, array $headers = [], array $context = []): JsonResponse;
}
