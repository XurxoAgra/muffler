<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

final class LoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        // Thrown by VerifiedUserChecker, after the password was accepted.
        if ($exception instanceof AccountStatusException) {
            return new JsonResponse([
                'error' => [
                    'code' => 'EMAIL_NOT_VERIFIED',
                    'message' => 'Email address has not been verified',
                ],
            ], 403);
        }

        return new JsonResponse([
            'error' => [
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'Invalid email or password',
            ],
        ], 401);
    }
}
