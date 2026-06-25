<?php

declare(strict_types=1);

namespace App\Maintenance\Application\UpdateMaintenanceRecord;

use App\Maintenance\Application\MaintenanceRecordDTO;
use App\Maintenance\Domain\Exception\InvoiceNotBelongingToVehicleException;
use App\Maintenance\Domain\Exception\InvoiceNotFoundException;
use App\Maintenance\Domain\Exception\MaintenanceRecordNotFoundException;
use App\Maintenance\Domain\InvoiceRepository;
use App\Maintenance\Domain\MaintenanceRecordRepository;

final readonly class UpdateMaintenanceRecordHandler
{
    public function __construct(
        private MaintenanceRecordRepository $maintenanceRecords,
        private InvoiceRepository $invoices,
    ) {
    }

    public function handle(UpdateMaintenanceRecordCommand $command): MaintenanceRecordDTO
    {
        $record = $this->maintenanceRecords->findById($command->maintenanceRecordId);

        if ($record === null) {
            throw new MaintenanceRecordNotFoundException("Maintenance record {$command->maintenanceRecordId} not found");
        }

        $invoice = null;

        if ($command->invoiceId !== null) {
            $invoice = $this->invoices->findById($command->invoiceId);

            if ($invoice === null) {
                throw new InvoiceNotFoundException("Invoice {$command->invoiceId} not found");
            }

            if ($invoice->getVehicle()->getId() !== $record->getVehicle()->getId()) {
                throw new InvoiceNotBelongingToVehicleException(
                    "Invoice {$command->invoiceId} does not belong to vehicle {$record->getVehicle()->getId()}",
                );
            }
        }

        $record->setServiceDate($command->serviceDate);
        $record->setType($command->type);
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
