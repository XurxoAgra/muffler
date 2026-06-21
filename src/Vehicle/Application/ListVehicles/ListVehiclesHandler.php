<?php

declare(strict_types=1);

namespace App\Vehicle\Application\ListVehicles;

use App\Vehicle\Application\VehicleDTO;
use App\Vehicle\Domain\VehicleUser;
use App\Vehicle\Domain\VehicleUserRepository;

final readonly class ListVehiclesHandler
{
    public function __construct(private VehicleUserRepository $vehicleUsers)
    {
    }

    /** @return VehicleDTO[] */
    public function handle(ListVehiclesQuery $query): array
    {
        $links = $this->vehicleUsers->findByUser($query->userId);

        return array_map(
            static fn (VehicleUser $link) => VehicleDTO::fromVehicle($link->getVehicle(), $link->getRole()),
            $links,
        );
    }
}
