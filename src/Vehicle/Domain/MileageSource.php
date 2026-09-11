<?php

declare(strict_types=1);

namespace App\Vehicle\Domain;

enum MileageSource: string
{
    case Manual = 'manual';
    case MaintenanceRecord = 'maintenance_record';
}
