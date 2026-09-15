<?php

declare(strict_types=1);

namespace App\Vehicle\Application\ListVehicleUsers;

final class ListVehicleUsersQuery
{
    public function __construct(
        public readonly string $vehicleId,
        public readonly string $requestingUserId,
    ) {
    }
}
