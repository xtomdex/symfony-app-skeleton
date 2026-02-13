<?php

declare(strict_types=1);

namespace App\Infrastructure\CommandBus\Trait;

use App\Domain\Contract\CommandInterface;
use App\Domain\DTO\CommandBusError;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait TwigCommandBusTrait
{
    use CommandBusTrait {
        handleCommand as private baseHandleCommand;
    }

    protected string $template = '';
    protected array $templateParams = [];
    protected string $loginRoute = 'app_login';
    protected ?FormInterface $form = null;
    protected array $formOptions = [];

    protected function handleForm(CommandInterface $command, Request $request): void
    {
        if (!$this->formClassname) {
            return;
        }

        if (defined("{$this->formClassname}::METHOD")) {
            $formMethod = constant("{$this->formClassname}::METHOD");
            if (in_array(strtoupper($formMethod), ['PATCH', 'PUT', 'DELETE'], true)) {
                $this->formOptions['method'] = 'POST';
            }
        }

        $this->form = $this->createForm($this->formClassname, $command, $this->formOptions);
        $this->form->handleRequest($request);
        $this->templateParams['form'] = $this->form->createView();

        if ($this->form->isSubmitted() && $this->form->isValid()) {
            $this->afterValidForm($command, $request);
        }
    }
    protected function contentResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        return $this->render($this->template, array_merge($this->templateParams, ['result' => $result]));
    }
    protected function errorResponse(CommandBusError $error): Response
    {
        // Redirect
        if ($error->redirect !== null) {
            return $this->redirectToRoute($error->redirect->route, $error->redirect->params, $error->redirect->statusCode);
        }

        // Validation -> attach errors to form and render template
        if ($error->statusCode === 400) {
            if ($this->form) {
                if (!empty($error->errors)) {
                    foreach ($error->errors as $field => $errorMessage) {
                        if ($this->form->has($field)) {
                            $this->form->get($field)->addError(new FormError($errorMessage));
                        } else {
                            $this->form->addError(new FormError($errorMessage));
                        }
                    }
                } else {
                    $this->form->addError(new FormError($error->message));
                }

                $this->templateParams['form'] = $this->form->createView();
            }

            return $this->render($this->template, $this->templateParams);
        }

        // Unauthorized -> login route
        if ($error->statusCode === 401) {
            return $this->redirectToRoute($this->loginRoute);
        }

        // Forbidden / Not found / Server error
        return match ($error->statusCode) {
            404 => $this->render('errors/not-found.html.twig'),
            403 => $this->render('errors/forbidden.html.twig'),
            default => $this->render('errors/server.html.twig', ['message' => $error->message]),
        };
    }

    // Special Twig override to handle request with existing but not submitted or not valid form
    protected function handleCommand(CommandInterface $command): mixed
    {
        if ($this->form && (!$this->form->isSubmitted() || !$this->form->isValid())) {
            return null;
        }

        return $this->baseHandleCommand($command);
    }

    // Special Twig lifecycle hook to add logic after form is validated
    protected function afterValidForm(CommandInterface $command, Request $request): void {}


    // Implemented in Symfony abstract controller
    abstract protected function render(string $view, array $parameters = [], ?Response $response = null): Response;
    abstract protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): Response;
}
