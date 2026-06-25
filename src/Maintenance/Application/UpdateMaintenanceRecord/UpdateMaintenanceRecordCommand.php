<?php

declare(strict_types=1);

namespace App\Maintenance\Application\UpdateMaintenanceRecord;

final class UpdateMaintenanceRecordCommand
{
    public function __construct(
        public readonly string $maintenanceRecordId,
        public readonly \DateTimeImmutable $serviceDate,
        public readonly string $type,
        public readonly ?int $mileage,
        public readonly ?string $notes,
        public readonly ?string $cost,
        public readonly ?string $shopName,
        public readonly ?\DateTimeImmutable $nextServiceDate,
        public readonly ?string $invoiceId,
    ) {
    }
}
