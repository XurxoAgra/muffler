<?php

declare(strict_types=1);

namespace App\Maintenance\Application\ListMaintenanceRecords;

final class ListMaintenanceRecordsQuery
{
    public function __construct(public readonly string $vehicleId)
    {
    }
}
