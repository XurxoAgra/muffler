<?php

declare(strict_types=1);

namespace App\Maintenance\Application\ExportVehicleMaintenance;

final readonly class ExportVehicleMaintenanceResult
{
    public function __construct(
        public string $content,
        public string $mimeType,
        public string $filename,
    ) {
    }
}
