<?php

declare(strict_types=1);

namespace App\Maintenance\Application\ExportVehicleMaintenance;

use App\Maintenance\Application\Port\ExportFormat;

final readonly class ExportVehicleMaintenanceQuery
{
    /**
     * @param array<string, string> $typeLabels maintenance record type key => display label, supplied by the
     *                                          client because translations live in the frontend
     */
    public function __construct(
        public string $vehicleId,
        public ExportFormat $format,
        public array $typeLabels = [],
    ) {
    }
}
