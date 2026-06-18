<?php

declare(strict_types=1);

namespace App\Auth\Domain\Token;

final class TokenPair
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $refreshToken,
    ) {
    }
}
