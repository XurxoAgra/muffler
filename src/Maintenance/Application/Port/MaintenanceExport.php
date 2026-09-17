<?php

declare(strict_types=1);

namespace App\Maintenance\Application\Port;

final readonly class MaintenanceExport
{
    /**
     * @param MaintenanceExportRecord[] $records   most recent service date first
     * @param string|null               $totalCost sum of the records' costs, null when none has a cost
     */
    public function __construct(
        public VehicleSummary $vehicle,
        public array $records,
        public ?string $totalCost,
    ) {
    }
}
