<?php

declare(strict_types=1);

namespace App\Vehicle\Application\Port;

final class UserSummary
{
    public function __construct(
        public readonly string $userId,
        public readonly string $email,
        public readonly string $firstName,
        public readonly string $lastName,
    ) {
    }
}
