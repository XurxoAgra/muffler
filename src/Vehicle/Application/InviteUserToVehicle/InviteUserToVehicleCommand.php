<?php

declare(strict_types=1);

namespace App\Vehicle\Application\InviteUserToVehicle;

final class InviteUserToVehicleCommand
{
    public function __construct(
        public readonly string $vehicleId,
        public readonly string $invitedByUserId,
        public readonly string $invitedUserEmail,
    ) {
    }
}
