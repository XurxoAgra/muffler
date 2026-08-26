<?php

declare(strict_types=1);

namespace App\Auth\Application\GetProfile;

use App\Auth\Domain\User\Exception\UserNotFoundException;
use App\Auth\Domain\User\UserId;
use App\Auth\Domain\User\UserRepository;
use App\Auth\Domain\User\UserRole;

final class GetProfileHandler
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function handle(GetProfileQuery $query): UserProfileDTO
    {
        $user = $this->users->findById(UserId::fromString($query->userId));

        if (null === $user) {
            throw new UserNotFoundException("User {$query->userId} not found");
        }

        return new UserProfileDTO(
            id: $user->id()->value(),
            email: $user->email()->value(),
            firstName: $user->firstName(),
            lastName: $user->lastName(),
            roles: array_map(fn (UserRole $r) => $r->value, $user->roles()),
            createdAt: $user->createdAt()->format(\DateTimeInterface::ATOM),
        );
    }
}
