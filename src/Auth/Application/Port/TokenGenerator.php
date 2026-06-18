<?php

declare(strict_types=1);

namespace App\Auth\Application\Port;

use App\Auth\Domain\Token\TokenPair;
use App\Auth\Domain\User\User;

interface TokenGenerator
{
    public function generatePair(User $user): TokenPair;

    public function generateAccessToken(User $user): string;
}
