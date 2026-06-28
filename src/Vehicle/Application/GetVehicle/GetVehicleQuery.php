<?php

declare(strict_types=1);

namespace App\Vehicle\Application\GetVehicle;

final class GetVehicleQuery
{
    public function __construct(
        public readonly string $vehicleId,
        public readonly string $userId,
    ) {
    }
}
