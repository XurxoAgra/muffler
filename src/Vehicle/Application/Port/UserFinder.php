<?php

declare(strict_types=1);

namespace App\Vehicle\Application\Port;

interface UserFinder
{
    public function findByEmail(string $email): ?UserSummary;

    public function findById(string $userId): ?UserSummary;
}
