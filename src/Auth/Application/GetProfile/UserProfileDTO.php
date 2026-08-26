<?php

declare(strict_types=1);

namespace App\Auth\Application\GetProfile;

final readonly class UserProfileDTO
{
    public function __construct(
        public string $id,
        public string $email,
        public string $firstName,
        public string $lastName,
        public array $roles,
        public string $createdAt,
    ) {
    }
}
