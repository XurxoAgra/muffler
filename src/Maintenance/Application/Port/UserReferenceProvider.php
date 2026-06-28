<?php

declare(strict_types=1);

namespace App\Maintenance\Application\Port;

use App\Auth\Domain\User\User;

interface UserReferenceProvider
{
    public function reference(string $userId): User;
}
