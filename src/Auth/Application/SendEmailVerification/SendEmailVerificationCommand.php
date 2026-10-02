<?php

declare(strict_types=1);

namespace App\Auth\Application\SendEmailVerification;

final readonly class SendEmailVerificationCommand
{
    public function __construct(
        public string $email,
    ) {
    }
}
