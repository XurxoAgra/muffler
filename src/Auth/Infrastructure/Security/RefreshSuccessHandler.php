<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Application\Port\TokenGenerator;
use App\Auth\Domain\User\UserId;
use App\Auth\Domain\User\UserRepository;
use Gesdinet\JWTRefreshTokenBundle\Security\Http\Authenticator\Token\PostRefreshTokenAuthenticationToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

// Keeps the refresh response shape (access_token/refresh_token) consistent
// with /api/auth/login and /api/auth/register, instead of Lexik's default
// "token" key.
final class RefreshSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly TokenGenerator $tokens,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        /** @var SymfonyUserAdapter $adapter */
        $adapter = $token->getUser();
        $user = $this->users->findById(UserId::fromString($adapter->userId));

        $refreshToken = $token instanceof PostRefreshTokenAuthenticationToken
            ? $token->getRefreshToken()->getRefreshToken()
            : null;

        return new JsonResponse([
            'access_token' => $this->tokens->generateAccessToken($user),
            'refresh_token' => $refreshToken,
        ]);
    }
}
