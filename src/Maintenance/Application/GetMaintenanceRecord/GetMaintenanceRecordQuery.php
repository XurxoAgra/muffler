<?php

declare(strict_types=1);

namespace App\Maintenance\Application\GetMaintenanceRecord;

final class GetMaintenanceRecordQuery
{
    public function __construct(public readonly string $maintenanceRecordId)
    {
    }
}
