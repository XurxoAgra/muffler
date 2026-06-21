<?php

declare(strict_types=1);

namespace App\Vehicle\Application\CreateVehicle;

use App\Vehicle\Application\VehicleCatalogResolver;
use App\Vehicle\Application\VehicleDTO;
use App\Vehicle\Domain\Vehicle;
use App\Vehicle\Domain\VehicleRepository;
use App\Vehicle\Domain\VehicleUser;
use App\Vehicle\Domain\VehicleUserRepository;
use App\Vehicle\Domain\VehicleUserRole;

final readonly class CreateVehicleHandler
{
    public function __construct(
        private VehicleRepository $vehicles,
        private VehicleUserRepository $vehicleUsers,
        private VehicleCatalogResolver $catalogResolver,
    ) {
    }

    public function handle(CreateVehicleCommand $command): VehicleDTO
    {
        [$make, $model] = $this->catalogResolver->resolve($command->makeId, $command->modelId);

        $vehicle = new Vehicle($command->plate, $command->year, $command->type, $command->userId);
        $vehicle->setVin($command->vin);
        $vehicle->setMake($make);
        $vehicle->setModel($model);
        $vehicle->setCustomMake($command->customMake);
        $vehicle->setCustomModel($command->customModel);

        // Vehicle must be persisted first so it has an id before the VehicleUser link references it.
        $this->vehicles->save($vehicle);

        $vehicleUser = new VehicleUser($vehicle, $command->userId, VehicleUserRole::Owner);
        $this->vehicleUsers->save($vehicleUser);

        return VehicleDTO::fromVehicle($vehicle, VehicleUserRole::Owner);
    }
}
