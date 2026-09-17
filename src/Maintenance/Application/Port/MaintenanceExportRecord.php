<?php

declare(strict_types=1);

namespace App\Maintenance\Application\Port;

final readonly class MaintenanceExportRecord
{
    public function __construct(
        public string $serviceDate,
        public string $type,
        public ?int $mileage,
        public ?string $cost,
        public ?string $shopName,
        public ?string $nextServiceDate,
        public bool $verified,
        public ?string $notes,
    ) {
    }
}
