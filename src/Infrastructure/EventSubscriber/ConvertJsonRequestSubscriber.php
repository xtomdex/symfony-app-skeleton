<?php

namespace App\Infrastructure\EventSubscriber;

use App\Domain\Exception\ValidationException;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use function json_last_error;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ConvertJsonRequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['convertJsonStringToArray', 10]
        ];
    }
    public function convertJsonStringToArray(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Allow webhook endpoints to keep raw body intact for signature verification.
        if ($request->attributes->getBoolean('skip_json_body_parsing')) {
            return;
        }

        $method = strtoupper($request->getMethod());
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return;
        }

        if ($request->getContentTypeFormat() !== 'json') {
            return;
        }

        $raw = (string) $request->getContent();
        if ($raw === '') {
            return;
        }

        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ValidationException('Invalid JSON body.', []);
        }

        $request->request->replace(is_array($data) ? $data : []);
    }
}
