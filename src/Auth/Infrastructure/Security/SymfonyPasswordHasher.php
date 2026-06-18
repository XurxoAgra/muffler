<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Application\Port\PasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final class SymfonyPasswordHasher implements PasswordHasher
{
    public function __construct(private readonly PasswordHasherFactoryInterface $factory)
    {
    }

    public function hash(string $rawPassword): string
    {
        return $this->factory->getPasswordHasher(SymfonyUserAdapter::class)->hash($rawPassword);
    }

    public function verify(string $hashedPassword, string $rawPassword): bool
    {
        return $this->factory->getPasswordHasher(SymfonyUserAdapter::class)->verify($hashedPassword, $rawPassword);
    }
}
