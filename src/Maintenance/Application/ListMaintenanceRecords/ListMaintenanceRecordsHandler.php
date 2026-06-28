<?php

declare(strict_types=1);

namespace App\Maintenance\Application\ListMaintenanceRecords;

use App\Maintenance\Application\MaintenanceRecordDTO;
use App\Maintenance\Domain\MaintenanceRecord;
use App\Maintenance\Domain\MaintenanceRecordRepository;

final readonly class ListMaintenanceRecordsHandler
{
    public function __construct(private MaintenanceRecordRepository $maintenanceRecords)
    {
    }

    /** @return MaintenanceRecordDTO[] */
    public function handle(ListMaintenanceRecordsQuery $query): array
    {
        $records = $this->maintenanceRecords->findByVehicleOrderedByServiceDateDesc($query->vehicleId);

        return array_map(
            static fn (MaintenanceRecord $record) => MaintenanceRecordDTO::fromMaintenanceRecord($record),
            $records,
        );
    }
}
