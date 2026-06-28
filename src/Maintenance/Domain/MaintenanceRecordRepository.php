<?php

declare(strict_types=1);

namespace App\Maintenance\Domain;

interface MaintenanceRecordRepository
{
    public function save(MaintenanceRecord $record): void;

    public function findById(string $id): ?MaintenanceRecord;

    public function remove(MaintenanceRecord $record): void;

    /** @return MaintenanceRecord[] */
    public function findByVehicleOrderedByServiceDateDesc(string $vehicleId): array;
}
