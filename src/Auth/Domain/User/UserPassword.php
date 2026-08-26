<?php

declare(strict_types=1);

namespace App\Auth\Domain\User;

// Wraps an ALREADY HASHED password. The raw password never enters the domain.
final class UserPassword
{
    private function __construct(private readonly string $hashedValue)
    {
    }

    public static function fromHash(string $hashedValue): self
    {
        if ('' === $hashedValue) {
            throw new \InvalidArgumentException('Hashed password cannot be empty');
        }

        return new self($hashedValue);
    }

    public function hashedValue(): string
    {
        return $this->hashedValue;
    }
}
