<?php

declare(strict_types=1);

namespace App\Maintenance\Application\GetMaintenanceRecord;

use App\Maintenance\Application\MaintenanceRecordDTO;
use App\Maintenance\Domain\Exception\MaintenanceRecordNotFoundException;
use App\Maintenance\Domain\MaintenanceRecordRepository;

final readonly class GetMaintenanceRecordHandler
{
    public function __construct(private MaintenanceRecordRepository $maintenanceRecords)
    {
    }

    public function handle(GetMaintenanceRecordQuery $query): MaintenanceRecordDTO
    {
        $record = $this->maintenanceRecords->findById($query->maintenanceRecordId);

        if (null === $record) {
            throw new MaintenanceRecordNotFoundException("Maintenance record {$query->maintenanceRecordId} not found");
        }

        return MaintenanceRecordDTO::fromMaintenanceRecord($record);
    }
}
