<?php

declare(strict_types=1);

namespace App\Auth\Application\Port;

use App\Auth\Domain\User\Exception\InvalidVerificationTokenException;
use App\Auth\Domain\User\Exception\VerificationTokenExpiredException;
use App\Auth\Domain\User\User;
use App\Auth\Domain\User\UserId;

// Stateless, signed email verification tokens. The signature covers the
// user's current email, so changing the email invalidates pending links.
interface VerificationTokenSigner
{
    public function sign(User $user): string;

    /**
     * @throws InvalidVerificationTokenException
     */
    public function extractUserId(string $token): UserId;

    /**
     * @throws InvalidVerificationTokenException
     * @throws VerificationTokenExpiredException
     */
    public function assertValidFor(string $token, User $user): void;
}
