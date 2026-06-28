<?php

declare(strict_types=1);

namespace App\Vehicle\Application\DeleteVehicle;

final class DeleteVehicleCommand
{
    public function __construct(
        public readonly string $vehicleId,
        public readonly string $userId,
    ) {
    }
}
