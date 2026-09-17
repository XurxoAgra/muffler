<?php

declare(strict_types=1);

namespace App\Maintenance\Application\Port;

interface VehicleFinder
{
    public function findById(string $vehicleId): ?VehicleSummary;
}
