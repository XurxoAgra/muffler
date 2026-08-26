<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Http;

use App\Auth\Application\GetProfile\GetProfileHandler;
use App\Auth\Application\GetProfile\GetProfileQuery;
use App\Auth\Infrastructure\Security\SymfonyUserAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/auth/me', name: 'auth.me', methods: ['GET'])]
final class MeController extends AbstractController
{
    public function __construct(
        private readonly GetProfileHandler $handler,
    ) {
    }

    public function __invoke(#[CurrentUser] SymfonyUserAdapter $authUser): JsonResponse
    {
        $profile = $this->handler->handle(new GetProfileQuery($authUser->userId));

        return $this->json([
            'id' => $profile->id,
            'email' => $profile->email,
            'first_name' => $profile->firstName,
            'last_name' => $profile->lastName,
            'roles' => $profile->roles,
            'created_at' => $profile->createdAt,
        ]);
    }
}
