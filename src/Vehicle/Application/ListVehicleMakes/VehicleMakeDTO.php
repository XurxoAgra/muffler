<?php

declare(strict_types=1);

namespace App\Vehicle\Application\ListVehicleMakes;

use App\Vehicle\Domain\VehicleMake;

final class VehicleMakeDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {
    }

    public static function fromVehicleMake(VehicleMake $make): self
    {
        return new self($make->getId(), $make->getName());
    }
}
