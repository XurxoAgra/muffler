<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Http;

use App\Auth\Application\VerifyEmail\VerifyEmailCommand;
use App\Auth\Application\VerifyEmail\VerifyEmailHandler;
use App\Auth\Infrastructure\Http\Request\VerifyEmailRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth/verify-email', name: 'auth.verify_email', methods: ['POST'])]
final class VerifyEmailController extends AbstractController
{
    public function __construct(private readonly VerifyEmailHandler $handler)
    {
    }

    public function __invoke(VerifyEmailRequest $request): Response
    {
        $this->handler->handle(new VerifyEmailCommand($request->token));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
