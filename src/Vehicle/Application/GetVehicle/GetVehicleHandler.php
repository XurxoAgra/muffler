<?php

declare(strict_types=1);

namespace App\Vehicle\Application\GetVehicle;

use App\Vehicle\Application\VehicleDTO;
use App\Vehicle\Domain\Exception\VehicleAccessDeniedException;
use App\Vehicle\Domain\Exception\VehicleNotFoundException;
use App\Vehicle\Domain\VehicleRepository;
use App\Vehicle\Domain\VehicleUserRepository;

final readonly class GetVehicleHandler
{
    public function __construct(
        private VehicleRepository $vehicles,
        private VehicleUserRepository $vehicleUsers,
    ) {
    }

    public function handle(GetVehicleQuery $query): VehicleDTO
    {
        $vehicle = $this->vehicles->findById($query->vehicleId);

        if ($vehicle === null) {
            throw new VehicleNotFoundException("Vehicle {$query->vehicleId} not found");
        }

        // TODO: replace with a VehicleVoter (e.g. denyAccessUnlessGranted('VIEW', $vehicle)) once it exists.
        $link = $this->vehicleUsers->findByVehicleAndUser($vehicle->getId(), $query->userId);

        if ($link === null) {
            throw new VehicleAccessDeniedException("User {$query->userId} has no access to vehicle {$query->vehicleId}");
        }

        return VehicleDTO::fromVehicle($vehicle, $link->getRole());
    }
}
