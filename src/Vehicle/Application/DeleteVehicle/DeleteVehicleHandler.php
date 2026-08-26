<?php

declare(strict_types=1);

namespace App\Vehicle\Application\DeleteVehicle;

use App\Vehicle\Domain\Exception\VehicleAccessDeniedException;
use App\Vehicle\Domain\Exception\VehicleNotFoundException;
use App\Vehicle\Domain\VehicleRepository;
use App\Vehicle\Domain\VehicleUserRepository;
use App\Vehicle\Domain\VehicleUserRole;

final readonly class DeleteVehicleHandler
{
    public function __construct(
        private VehicleRepository $vehicles,
        private VehicleUserRepository $vehicleUsers,
    ) {
    }

    public function handle(DeleteVehicleCommand $command): void
    {
        $vehicle = $this->vehicles->findById($command->vehicleId);

        if (null === $vehicle) {
            throw new VehicleNotFoundException("Vehicle {$command->vehicleId} not found");
        }

        $link = $this->vehicleUsers->findByVehicleAndUser($vehicle->getId(), $command->userId);

        if (null === $link || VehicleUserRole::Owner !== $link->getRole()) {
            throw new VehicleAccessDeniedException("User {$command->userId} cannot delete vehicle {$command->vehicleId}");
        }

        $this->vehicles->remove($vehicle);
    }
}
