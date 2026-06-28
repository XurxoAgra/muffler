<?php

declare(strict_types=1);

namespace App\Vehicle\Application\ImportVehicleCatalog;

final readonly class VehicleCatalogRow
{
    public function __construct(
        public string $make,
        public string $model,
    ) {
    }
}
