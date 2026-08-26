<?php

declare(strict_types=1);

namespace App\Vehicle\Application\ListVehicleModels;

use App\Vehicle\Domain\Exception\VehicleMakeNotFoundException;
use App\Vehicle\Domain\VehicleMakeRepository;
use App\Vehicle\Domain\VehicleModelRepository;

final readonly class ListVehicleModelsHandler
{
    public function __construct(
        private VehicleMakeRepository $makes,
        private VehicleModelRepository $models,
    ) {
    }

    /** @return VehicleModelDTO[] */
    public function handle(ListVehicleModelsQuery $query): array
    {
        if (null === $this->makes->findById($query->makeId)) {
            throw new VehicleMakeNotFoundException("Vehicle make {$query->makeId} not found");
        }

        return array_map(
            VehicleModelDTO::fromVehicleModel(...),
            $this->models->findByMakeOrderedByName($query->makeId),
        );
    }
}
