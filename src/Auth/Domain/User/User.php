<?php

declare(strict_types=1);

namespace App\Auth\Domain\User;

use App\Auth\Domain\Event\UserWasRegistered;
use App\Shared\Domain\Aggregate\AggregateRoot;

final class User extends AggregateRoot
{
    private function __construct(
        private readonly UserId $id,
        private UserEmail $email,
        private UserPassword $password,
        private readonly string $firstName,
        private readonly string $lastName,
        private array $roles,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function register(
        UserId $id,
        UserEmail $email,
        UserPassword $password,
        string $firstName,
        string $lastName,
        array $roles = [],
    ): self {
        if ($roles === []) {
            $roles = [UserRole::User];
        }

        $now = new \DateTimeImmutable();
        $user = new self($id, $email, $password, $firstName, $lastName, $roles, $now, $now);

        $user->recordEvent(new UserWasRegistered($id, $email, $now));

        return $user;
    }

    public static function reconstitute(
        UserId $id,
        UserEmail $email,
        UserPassword $password,
        string $firstName,
        string $lastName,
        array $roles,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $email, $password, $firstName, $lastName, $roles, $createdAt, $updatedAt);
    }

    public function changeEmail(UserEmail $newEmail): void
    {
        $this->email = $newEmail;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function changePassword(UserPassword $newPassword): void
    {
        $this->password = $newPassword;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function email(): UserEmail
    {
        return $this->email;
    }

    public function password(): UserPassword
    {
        return $this->password;
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function lastName(): string
    {
        return $this->lastName;
    }

    /** @return UserRole[] */
    public function roles(): array
    {
        return $this->roles;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function hasRole(UserRole $role): bool
    {
        return in_array($role, $this->roles, strict: true);
    }
}
