<?php

declare(strict_types=1);

namespace App\Vehicle\Application\UpdateVehicle;

use App\Vehicle\Application\VehicleCatalogResolver;
use App\Vehicle\Application\VehicleDTO;
use App\Vehicle\Domain\Exception\VehicleAccessDeniedException;
use App\Vehicle\Domain\Exception\VehicleNotFoundException;
use App\Vehicle\Domain\VehicleRepository;
use App\Vehicle\Domain\VehicleUserRepository;

final readonly class UpdateVehicleHandler
{
    public function __construct(
        private VehicleRepository $vehicles,
        private VehicleUserRepository $vehicleUsers,
        private VehicleCatalogResolver $catalogResolver,
    ) {
    }

    public function handle(UpdateVehicleCommand $command): VehicleDTO
    {
        $vehicle = $this->vehicles->findById($command->vehicleId);

        if ($vehicle === null) {
            throw new VehicleNotFoundException("Vehicle {$command->vehicleId} not found");
        }

        $link = $this->vehicleUsers->findByVehicleAndUser($vehicle->getId(), $command->userId);

        if ($link === null) {
            throw new VehicleAccessDeniedException("User {$command->userId} has no access to vehicle {$command->vehicleId}");
        }

        [$make, $model] = $this->catalogResolver->resolve($command->makeId, $command->modelId);

        $vehicle->setPlate($command->plate);
        $vehicle->setYear($command->year);
        $vehicle->setType($command->type);
        $vehicle->setVin($command->vin);
        $vehicle->setMake($make);
        $vehicle->setModel($model);
        $vehicle->setCustomMake($command->customMake);
        $vehicle->setCustomModel($command->customModel);

        $this->vehicles->save($vehicle);

        return VehicleDTO::fromVehicle($vehicle, $link->getRole());
    }
}
