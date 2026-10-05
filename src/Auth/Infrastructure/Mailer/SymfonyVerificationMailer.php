<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Mailer;

use App\Auth\Application\Port\VerificationMailer;
use App\Auth\Domain\User\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class SymfonyVerificationMailer implements VerificationMailer
{
    private const FRONTEND_PATH = '/verify-email';

    public function __construct(
        private MailerInterface $mailer,
        #[Autowire('%env(MAILER_FROM)%')]
        private string $from,
        #[Autowire('%env(FRONTEND_URL)%')]
        private string $frontendUrl,
    ) {
    }

    public function send(User $user, string $token): void
    {
        $link = rtrim($this->frontendUrl, '/').self::FRONTEND_PATH.'?'.http_build_query(['token' => $token]);
        $name = htmlspecialchars($user->firstName(), ENT_QUOTES);
        $href = htmlspecialchars($link, ENT_QUOTES);

        $this->mailer->send((new Email())
            ->from($this->from)
            ->to($user->email()->value())
            ->subject('Activate your Muffler account')
            ->text(<<<TEXT
                Hi {$user->firstName()},

                Confirm your email address to activate your Muffler account:

                {$link}

                If you did not create this account, you can ignore this email.
                TEXT)
            ->html(<<<HTML
                <p>Hi {$name},</p>
                <p>Confirm your email address to activate your Muffler account:</p>
                <p><a href="{$href}">Activate my account</a></p>
                <p>If you did not create this account, you can ignore this email.</p>
                HTML));
    }
}
