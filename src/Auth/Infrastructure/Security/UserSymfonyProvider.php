<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Domain\User\User;
use App\Auth\Domain\User\UserEmail;
use App\Auth\Domain\User\UserRepository;
use App\Auth\Domain\User\UserRole;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class UserSymfonyProvider implements UserProviderInterface
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->users->findByEmail(new UserEmail($identifier));

        if (null === $user) {
            throw new UserNotFoundException("User {$identifier} not found");
        }

        return $this->fromDomain($user);
    }

    public function fromDomain(User $user): SymfonyUserAdapter
    {
        return new SymfonyUserAdapter(
            identifier: $user->email()->value(),
            password: $user->password()->hashedValue(),
            roles: array_map(fn (UserRole $r) => $r->value, $user->roles()),
            userId: $user->id()->value(),
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return SymfonyUserAdapter::class === $class;
    }
}
