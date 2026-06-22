<?php

declare(strict_types=1);

namespace App\Vehicle\Application\ImportVehicleCatalog;

final readonly class VehicleCatalogImportResult
{
    public function __construct(
        public int $makesCreated = 0,
        public int $makesExisting = 0,
        public int $modelsCreated = 0,
        public int $modelsExisting = 0,
    ) {
    }
}
