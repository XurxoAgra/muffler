<?php

declare(strict_types=1);

namespace App\Maintenance\Application;

use App\Maintenance\Domain\MaintenanceRecord;

final class MaintenanceRecordDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $vehicleId,
        public readonly ?string $invoiceId,
        public readonly string $createdById,
        public readonly string $serviceDate,
        public readonly ?int $mileage,
        public readonly string $maintenanceRecordTypeId,
        public readonly ?string $notes,
        public readonly ?string $cost,
        public readonly ?string $shopName,
        public readonly ?string $nextServiceDate,
        public readonly bool $verified,
        public readonly string $createdAt,
    ) {
    }

    public static function fromMaintenanceRecord(MaintenanceRecord $record): self
    {
        return new self(
            id: $record->getId()->toRfc4122(),
            vehicleId: $record->getVehicle()->getId(),
            invoiceId: $record->getInvoice()?->getId()->toRfc4122(),
            createdById: $record->getCreatedBy()->id()->value(),
            serviceDate: $record->getServiceDate()->format('Y-m-d'),
            mileage: $record->getMileage(),
            maintenanceRecordTypeId: $record->getMaintenanceRecordType()->getId()->toRfc4122(),
            notes: $record->getNotes(),
            cost: $record->getCost(),
            shopName: $record->getShopName(),
            nextServiceDate: $record->getNextServiceDate()?->format('Y-m-d'),
            verified: $record->isVerified(),
            createdAt: $record->getCreatedAt()->format(DATE_ATOM),
        );
    }
}
