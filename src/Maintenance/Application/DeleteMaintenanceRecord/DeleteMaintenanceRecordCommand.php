<?php

declare(strict_types=1);

namespace App\Maintenance\Application\DeleteMaintenanceRecord;

final class DeleteMaintenanceRecordCommand
{
    public function __construct(public readonly string $maintenanceRecordId)
    {
    }
}
