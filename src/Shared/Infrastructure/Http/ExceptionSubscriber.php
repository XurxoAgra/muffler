<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http;

use App\Auth\Domain\Exception\DomainException;
use App\Auth\Domain\User\Exception\InvalidEmailException;
use App\Auth\Domain\User\Exception\UserAlreadyExistsException;
use App\Auth\Domain\User\Exception\UserNotFoundException;
use App\Shared\Application\Exception\ValidationException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
final class ExceptionSubscriber
{
    private const MAP = [
        UserAlreadyExistsException::class => [409, 'EMAIL_TAKEN'],
        UserNotFoundException::class => [404, 'USER_NOT_FOUND'],
        InvalidEmailException::class => [400, 'INVALID_EMAIL'],
    ];

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Let Symfony handle HTTP exceptions normally (404, 405, etc.)
        if ($exception instanceof HttpExceptionInterface) {
            return;
        }

        if ($exception instanceof ValidationException) {
            $event->setResponse(new JsonResponse([
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => $exception->getMessage(),
                    'details' => $exception->details(),
                ],
            ], 400));

            return;
        }

        [$status, $code] = $this->resolveStatus($exception);

        $event->setResponse(new JsonResponse([
            'error' => [
                'code' => $code,
                'message' => $exception->getMessage(),
            ],
        ], $status));
    }

    private function resolveStatus(\Throwable $e): array
    {
        foreach (self::MAP as $class => [$status, $code]) {
            if ($e instanceof $class) {
                return [$status, $code];
            }
        }

        return $e instanceof DomainException
            ? [422, 'DOMAIN_ERROR']
            : [500, 'INTERNAL_ERROR'];
    }
}
