<?php

declare(strict_types=1);

namespace App\Vehicle\Domain;

interface MileageRecordRepository
{
    public function save(MileageRecord $record): void;

    /** @return MileageRecord[] */
    public function findByVehicleOrderedByRecordedAtDesc(string $vehicleId): array;

    public function findLatestByVehicle(string $vehicleId): ?MileageRecord;
}
