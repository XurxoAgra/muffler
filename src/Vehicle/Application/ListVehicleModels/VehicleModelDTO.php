<?php

declare(strict_types=1);

namespace App\Vehicle\Application\ListVehicleModels;

use App\Vehicle\Domain\VehicleModel;

final class VehicleModelDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {
    }

    public static function fromVehicleModel(VehicleModel $model): self
    {
        return new self($model->getId(), $model->getName());
    }
}
