<?php

declare(strict_types=1);

namespace App\Maintenance\Application\DeleteMaintenanceRecord;

use App\Maintenance\Domain\Exception\MaintenanceRecordNotFoundException;
use App\Maintenance\Domain\MaintenanceRecordRepository;

final readonly class DeleteMaintenanceRecordHandler
{
    public function __construct(private MaintenanceRecordRepository $maintenanceRecords)
    {
    }

    public function handle(DeleteMaintenanceRecordCommand $command): void
    {
        $record = $this->maintenanceRecords->findById($command->maintenanceRecordId);

        if ($record === null) {
            throw new MaintenanceRecordNotFoundException("Maintenance record {$command->maintenanceRecordId} not found");
        }

        $this->maintenanceRecords->remove($record);
    }
}
