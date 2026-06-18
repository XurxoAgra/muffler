<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;

// Keeps every JWT-protected-route failure (missing/invalid/expired token)
// in the project's standard {"error": {...}} envelope instead of Lexik's
// default {"code","message"} shape.
#[AsEventListener(event: Events::AUTHENTICATION_FAILURE)]
#[AsEventListener(event: Events::JWT_NOT_FOUND)]
#[AsEventListener(event: Events::JWT_INVALID)]
#[AsEventListener(event: Events::JWT_EXPIRED)]
final class JwtFailureListener
{
    public function __invoke(AuthenticationFailureEvent $event): void
    {
        $event->setResponse(new JsonResponse([
            'error' => [
                'code' => 'INVALID_CREDENTIALS',
                'message' => $event->getException()->getMessage(),
            ],
        ], 401));
    }
}
