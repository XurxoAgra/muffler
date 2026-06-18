<?php

declare(strict_types=1);

namespace App\Auth\Domain\User;

use App\Auth\Domain\User\Exception\InvalidEmailException;

final class UserEmail
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $normalised = strtolower(trim($value));

        if (!filter_var($normalised, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException("Invalid email address: {$value}");
        }

        $this->value = $normalised;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
