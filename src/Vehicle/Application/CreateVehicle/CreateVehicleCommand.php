<?php

declare(strict_types=1);

namespace App\Vehicle\Application\CreateVehicle;

final class CreateVehicleCommand
{
    public function __construct(
        public readonly string $userId,
        public readonly string $plate,
        public readonly int $year,
        public readonly string $type,
        public readonly ?string $vin,
        public readonly ?string $makeId,
        public readonly ?string $modelId,
        public readonly ?string $customMake,
        public readonly ?string $customModel,
    ) {
    }
}
