<?php

declare(strict_types=1);

namespace App\Auth\Application\Port;

interface PasswordHasher
{
    public function hash(string $rawPassword): string;

    public function verify(string $hashedPassword, string $rawPassword): bool;
}
