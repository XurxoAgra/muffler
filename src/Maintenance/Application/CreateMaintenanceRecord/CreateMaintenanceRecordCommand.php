<?php

declare(strict_types=1);

namespace App\Maintenance\Application\CreateMaintenanceRecord;

final class CreateMaintenanceRecordCommand
{
    public function __construct(
        public readonly string $vehicleId,
        public readonly string $userId,
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
