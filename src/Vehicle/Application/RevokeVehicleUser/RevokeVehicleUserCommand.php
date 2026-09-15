<?php

declare(strict_types=1);

namespace App\Vehicle\Application\RevokeVehicleUser;

final class RevokeVehicleUserCommand
{
    public function __construct(
        public readonly string $vehicleId,
        public readonly string $revokedByUserId,
        public readonly string $targetUserId,
    ) {
    }
}
