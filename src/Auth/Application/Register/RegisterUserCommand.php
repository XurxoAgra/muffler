<?php

declare(strict_types=1);

namespace App\Auth\Application\Register;

final class RegisterUserCommand
{
    public function __construct(
        public readonly string $email,
        public readonly string $rawPassword,
        public readonly string $firstName,
        public readonly string $lastName,
    ) {
    }
}
