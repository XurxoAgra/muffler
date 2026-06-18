<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Application\Port\TokenGenerator;
use App\Auth\Domain\Event\UserLoggedIn;
use App\Auth\Domain\User\UserEmail;
use App\Auth\Domain\User\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private readonly TokenGenerator $tokens,
        private readonly UserRepository $users,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        $user = $this->users->findByEmail(new UserEmail($token->getUserIdentifier()));

        $pair = $this->tokens->generatePair($user);

        $this->dispatcher->dispatch(new UserLoggedIn($user->id(), new \DateTimeImmutable()));

        return new JsonResponse([
            'access_token' => $pair->accessToken,
            'refresh_token' => $pair->refreshToken,
        ]);
    }
}
