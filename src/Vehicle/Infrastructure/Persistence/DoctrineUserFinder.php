<?php

declare(strict_types=1);

namespace App\Vehicle\Infrastructure\Persistence;

use App\Auth\Domain\User\Exception\InvalidEmailException;
use App\Auth\Domain\User\Exception\InvalidUserIdException;
use App\Auth\Domain\User\User;
use App\Auth\Domain\User\UserEmail;
use App\Auth\Domain\User\UserId;
use App\Auth\Domain\User\UserRepository;
use App\Vehicle\Application\Port\UserFinder;
use App\Vehicle\Application\Port\UserSummary;

final readonly class DoctrineUserFinder implements UserFinder
{
    public function __construct(private UserRepository $users)
    {
    }

    public function findByEmail(string $email): ?UserSummary
    {
        try {
            $emailVo = new UserEmail($email);
        } catch (InvalidEmailException) {
            return null;
        }

        $user = $this->users->findByEmail($emailVo);

        return null !== $user ? $this->toSummary($user) : null;
    }

    public function findById(string $userId): ?UserSummary
    {
        try {
            $userIdVo = UserId::fromString($userId);
        } catch (InvalidUserIdException) {
            return null;
        }

        $user = $this->users->findById($userIdVo);

        return null !== $user ? $this->toSummary($user) : null;
    }

    private function toSummary(User $user): UserSummary
    {
        return new UserSummary(
            userId: $user->id()->value(),
            email: $user->email()->value(),
            firstName: $user->firstName(),
            lastName: $user->lastName(),
        );
    }
}
