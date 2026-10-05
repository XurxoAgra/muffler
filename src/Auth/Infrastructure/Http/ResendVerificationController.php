<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Http;

use App\Auth\Application\SendEmailVerification\SendEmailVerificationCommand;
use App\Auth\Application\SendEmailVerification\SendEmailVerificationHandler;
use App\Auth\Infrastructure\Http\Request\ResendVerificationRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Always 202, whether or not the email belongs to an unverified account.
#[Route('/api/auth/verify-email/resend', name: 'auth.verify_email.resend', methods: ['POST'])]
final class ResendVerificationController extends AbstractController
{
    public function __construct(private readonly SendEmailVerificationHandler $handler)
    {
    }

    public function __invoke(ResendVerificationRequest $request): Response
    {
        $this->handler->handle(new SendEmailVerificationCommand($request->email));

        return new Response(status: Response::HTTP_ACCEPTED);
    }
}
