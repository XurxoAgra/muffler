<?php

declare(strict_types=1);

namespace App\Vehicle\Application;

use App\Vehicle\Domain\Vehicle;
use App\Vehicle\Domain\VehicleType;
use App\Vehicle\Domain\VehicleUserRole;

final class VehicleDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $plate,
        public readonly int $year,
        public readonly VehicleType $type,
        public readonly ?string $vin,
        public readonly ?array $make,
        public readonly ?array $model,
        public readonly ?string $customMake,
        public readonly ?string $customModel,
        public readonly string $role,
    ) {
    }

    public static function fromVehicle(Vehicle $vehicle, VehicleUserRole $role): self
    {
        $make = $vehicle->getMake();
        $model = $vehicle->getModel();

        return new self(
            id: $vehicle->getId(),
            plate: $vehicle->getPlate(),
            year: $vehicle->getYear(),
            type: $vehicle->getType(),
            vin: $vehicle->getVin(),
            make: null !== $make ? ['id' => $make->getId(), 'name' => $make->getName()] : null,
            model: null !== $model ? ['id' => $model->getId(), 'name' => $model->getName()] : null,
            customMake: $vehicle->getCustomMake(),
            customModel: $vehicle->getCustomModel(),
            role: $role->value,
        );
    }
}
