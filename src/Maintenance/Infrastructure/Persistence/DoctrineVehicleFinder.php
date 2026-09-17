<?php

declare(strict_types=1);

namespace App\Maintenance\Infrastructure\Persistence;

use App\Maintenance\Application\Port\VehicleFinder;
use App\Maintenance\Application\Port\VehicleSummary;
use App\Vehicle\Domain\Vehicle;
use App\Vehicle\Domain\VehicleRepository;

final readonly class DoctrineVehicleFinder implements VehicleFinder
{
    public function __construct(private VehicleRepository $vehicles)
    {
    }

    public function findById(string $vehicleId): ?VehicleSummary
    {
        $vehicle = $this->vehicles->findById($vehicleId);

        return null !== $vehicle ? $this->toSummary($vehicle) : null;
    }

    private function toSummary(Vehicle $vehicle): VehicleSummary
    {
        return new VehicleSummary(
            vehicleId: $vehicle->getId(),
            plate: $vehicle->getPlate(),
            make: $vehicle->getMake()?->getName() ?? $vehicle->getCustomMake(),
            model: $vehicle->getModel()?->getName() ?? $vehicle->getCustomModel(),
            year: $vehicle->getYear(),
            vin: $vehicle->getVin(),
        );
    }
}
