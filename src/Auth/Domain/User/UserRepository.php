<?php

declare(strict_types=1);

namespace App\Auth\Domain\User;

interface UserRepository
{
    public function save(User $user): void;

    public function findById(UserId $id): ?User;

    public function findByEmail(UserEmail $email): ?User;

    public function nextId(): UserId;
}
