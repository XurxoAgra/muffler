<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Http;

use App\Auth\Application\Register\RegisterUserCommand;
use App\Auth\Application\Register\RegisterUserHandler;
use App\Auth\Infrastructure\Http\Request\RegisterRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth/register', name: 'auth.register', methods: ['POST'])]
final class RegisterController extends AbstractController
{
    public function __construct(private readonly RegisterUserHandler $handler)
    {
    }

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $tokens = $this->handler->handle(new RegisterUserCommand(
            email: $request->email,
            rawPassword: $request->password,
            firstName: $request->firstName,
            lastName: $request->lastName,
        ));

        return $this->json([
            'access_token' => $tokens->accessToken,
            'refresh_token' => $tokens->refreshToken,
        ], 201);
    }
}
