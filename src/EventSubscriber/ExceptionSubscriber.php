<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\HttpFoundation\Response;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => 'onException',
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $message = 'Internal Server Error';
        $errors = new \stdClass();

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $message = $exception->getMessage();
        }

        if ($exception instanceof ValidationFailedException) {
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            $message = 'Validation failed';

            $formattedErrors = [];

            foreach ($exception->getViolations() as $violation) {
                $formattedErrors[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            $errors = $formattedErrors;
        }

        $response = [
            'message' => $message,
            'code' => $statusCode,
            'errors' => $errors,
        ];

        $event->setResponse(new JsonResponse($response, $statusCode));
    }
}
