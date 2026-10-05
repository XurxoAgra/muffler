<?php

declare(strict_types=1);

namespace App\Auth\Application\SendEmailVerification;

use App\Auth\Application\Port\VerificationMailer;
use App\Auth\Application\Port\VerificationTokenSigner;
use App\Auth\Domain\User\Exception\InvalidEmailException;
use App\Auth\Domain\User\UserEmail;
use App\Auth\Domain\User\UserRepository;

// Silently does nothing for unknown or already verified emails, so the
// public resend endpoint cannot be used to probe which accounts exist.
final readonly class SendEmailVerificationHandler
{
    public function __construct(
        private UserRepository $users,
        private VerificationTokenSigner $signer,
        private VerificationMailer $mailer,
    ) {
    }

    public function handle(SendEmailVerificationCommand $command): void
    {
        try {
            $email = new UserEmail($command->email);
        } catch (InvalidEmailException) {
            return;
        }

        $user = $this->users->findByEmail($email);

        if (null === $user || $user->isVerified()) {
            return;
        }

        $this->mailer->send($user, $this->signer->sign($user));
    }
}
