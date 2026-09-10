<?php

declare(strict_types=1);

namespace App\Maintenance\Application\UpdateMaintenanceRecord;

use App\Maintenance\Application\MaintenanceRecordDTO;
use App\Maintenance\Domain\Exception\InvoiceNotBelongingToVehicleException;
use App\Maintenance\Domain\Exception\InvoiceNotFoundException;
use App\Maintenance\Domain\Exception\MaintenanceRecordNotFoundException;
use App\Maintenance\Domain\Exception\MaintenanceRecordTypeInactiveException;
use App\Maintenance\Domain\Exception\MaintenanceRecordTypeNotFoundException;
use App\Maintenance\Domain\InvoiceRepository;
use App\Maintenance\Domain\MaintenanceRecordRepository;
use App\Maintenance\Domain\MaintenanceRecordTypeRepository;

final readonly class UpdateMaintenanceRecordHandler
{
    public function __construct(
        private MaintenanceRecordRepository $maintenanceRecords,
        private InvoiceRepository $invoices,
        private MaintenanceRecordTypeRepository $maintenanceRecordTypes,
    ) {
    }

    public function handle(UpdateMaintenanceRecordCommand $command): MaintenanceRecordDTO
    {
        $record = $this->maintenanceRecords->findById($command->maintenanceRecordId);

        if (null === $record) {
            throw new MaintenanceRecordNotFoundException("Maintenance record {$command->maintenanceRecordId} not found");
        }

        $type = $this->maintenanceRecordTypes->findById($command->maintenanceRecordTypeId);

        if (null === $type) {
            throw new MaintenanceRecordTypeNotFoundException("Maintenance record type {$command->maintenanceRecordTypeId} not found");
        }

        if (!$type->isActive()) {
            throw new MaintenanceRecordTypeInactiveException("Maintenance record type {$command->maintenanceRecordTypeId} is not active");
        }

        $invoice = null;

        if (null !== $command->invoiceId) {
            $invoice = $this->invoices->findById($command->invoiceId);

            if (null === $invoice) {
                throw new InvoiceNotFoundException("Invoice {$command->invoiceId} not found");
            }

            if ($invoice->getVehicle()->getId() !== $record->getVehicle()->getId()) {
                throw new InvoiceNotBelongingToVehicleException("Invoice {$command->invoiceId} does not belong to vehicle {$record->getVehicle()->getId()}");
            }
        }

        $record->setServiceDate($command->serviceDate);
        $record->setMaintenanceRecordType($type);
        $record->setMileage($command->mileage);
        $record->setNotes($command->notes);
        $record->setCost($command->cost);
        $record->setShopName($command->shopName);
        $record->setNextServiceDate($command->nextServiceDate);
        $record->setInvoice($invoice);

        $this->maintenanceRecords->save($record);

        return MaintenanceRecordDTO::fromMaintenanceRecord($record);
    }
}
