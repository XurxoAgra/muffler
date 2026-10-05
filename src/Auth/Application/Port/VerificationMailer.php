<?php

declare(strict_types=1);

namespace App\Auth\Application\Port;

use App\Auth\Domain\User\User;

interface VerificationMailer
{
    public function send(User $user, string $token): void;
}
