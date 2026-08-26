<?php

declare(strict_types=1);

namespace App\Maintenance\Application\CreateMaintenanceRecord;

use App\Maintenance\Application\MaintenanceRecordDTO;
use App\Maintenance\Application\Port\UserReferenceProvider;
use App\Maintenance\Domain\Exception\InvoiceNotBelongingToVehicleException;
use App\Maintenance\Domain\Exception\InvoiceNotFoundException;
use App\Maintenance\Domain\InvoiceRepository;
use App\Maintenance\Domain\MaintenanceRecord;
use App\Maintenance\Domain\MaintenanceRecordRepository;
use App\Vehicle\Domain\Exception\VehicleNotFoundException;
use App\Vehicle\Domain\VehicleRepository;

final readonly class CreateMaintenanceRecordHandler
{
    public function __construct(
        private VehicleRepository $vehicles,
        private InvoiceRepository $invoices,
        private MaintenanceRecordRepository $maintenanceRecords,
        private UserReferenceProvider $userReferences,
    ) {
    }

    public function handle(CreateMaintenanceRecordCommand $command): MaintenanceRecordDTO
    {
        $vehicle = $this->vehicles->findById($command->vehicleId);

        if (null === $vehicle) {
            throw new VehicleNotFoundException("Vehicle {$command->vehicleId} not found");
        }

        $invoice = null;

        if (null !== $command->invoiceId) {
            $invoice = $this->invoices->findById($command->invoiceId);

            if (null === $invoice) {
                throw new InvoiceNotFoundException("Invoice {$command->invoiceId} not found");
            }

            if ($invoice->getVehicle()->getId() !== $command->vehicleId) {
                throw new InvoiceNotBelongingToVehicleException("Invoice {$command->invoiceId} does not belong to vehicle {$command->vehicleId}");
            }
        }

        $record = new MaintenanceRecord(
            vehicle: $vehicle,
            createdBy: $this->userReferences->reference($command->userId),
            serviceDate: $command->serviceDate,
            type: $command->type,
            invoice: $invoice,
        );
        $record->setMileage($command->mileage);
        $record->setNotes($command->notes);
        $record->setCost($command->cost);
        $record->setShopName($command->shopName);
        $record->setNextServiceDate($command->nextServiceDate);

        $this->maintenanceRecords->save($record);

        return MaintenanceRecordDTO::fromMaintenanceRecord($record);
    }
}
