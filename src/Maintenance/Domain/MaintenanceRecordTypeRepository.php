<?php

declare(strict_types=1);

namespace App\Maintenance\Domain;

interface MaintenanceRecordTypeRepository
{
    /** @return MaintenanceRecordType[] */
    public function findAllOrderedByKey(): array;

    public function findById(string $id): ?MaintenanceRecordType;
}
