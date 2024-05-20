<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ExceptionListener
{
    #[AsEventListener(event: KernelEvents::EXCEPTION)]
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $response = new JsonResponse(['message' => $exception->getMessage()], $exception->getStatusCode());
        $event->setResponse($response);
    }
}
