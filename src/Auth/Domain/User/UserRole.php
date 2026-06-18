<?php

declare(strict_types=1);

namespace App\Auth\Domain\User;

enum UserRole: string
{
    case User = 'ROLE_USER';
    case Admin = 'ROLE_ADMIN';

    public static function fromString(string $value): self
    {
        return self::from($value);
    }
}
