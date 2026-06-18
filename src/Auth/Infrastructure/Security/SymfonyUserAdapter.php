<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

// Thin wrapper over the domain User — only exists to satisfy Symfony's
// security interfaces. Never leaks into Domain or Application.
final class SymfonyUserAdapter implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct(
        private readonly string $identifier,
        private readonly string $password,
        private readonly array $roles,
        public readonly string $userId,
    ) {
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
    }
}
