<?php

declare(strict_types=1);

namespace App\Maintenance\Application\Port;

final readonly class VehicleSummary
{
    /**
     * @param string|null $make  catalog make name, or the custom make when the vehicle has none
     * @param string|null $model catalog model name, or the custom model when the vehicle has none
     */
    public function __construct(
        public string $vehicleId,
        public string $plate,
        public ?string $make,
        public ?string $model,
        public int $year,
        public ?string $vin,
    ) {
    }
}
