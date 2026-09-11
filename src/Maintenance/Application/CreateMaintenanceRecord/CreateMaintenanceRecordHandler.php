<?php

declare(strict_types=1);

namespace App\Maintenance\Application\CreateMaintenanceRecord;

use App\Maintenance\Application\MaintenanceRecordDTO;
use App\Maintenance\Application\Port\UserReferenceProvider;
use App\Maintenance\Domain\Exception\InvoiceNotBelongingToVehicleException;
use App\Maintenance\Domain\Exception\InvoiceNotFoundException;
use App\Maintenance\Domain\Exception\MaintenanceRecordTypeInactiveException;
use App\Maintenance\Domain\Exception\MaintenanceRecordTypeNotFoundException;
use App\Maintenance\Domain\InvoiceRepository;
use App\Maintenance\Domain\MaintenanceRecord;
use App\Maintenance\Domain\MaintenanceRecordRepository;
use App\Maintenance\Domain\MaintenanceRecordTypeRepository;
use App\Vehicle\Domain\Exception\VehicleNotFoundException;
use App\Vehicle\Domain\MileageRecord;
use App\Vehicle\Domain\MileageRecordRepository;
use App\Vehicle\Domain\MileageSource;
use App\Vehicle\Domain\Vehicle;
use App\Vehicle\Domain\VehicleRepository;

final readonly class CreateMaintenanceRecordHandler
{
    public function __construct(
        private VehicleRepository $vehicles,
        private InvoiceRepository $invoices,
        private MaintenanceRecordRepository $maintenanceRecords,
        private MaintenanceRecordTypeRepository $maintenanceRecordTypes,
        private UserReferenceProvider $userReferences,
        private MileageRecordRepository $mileageRecords,
    ) {
    }

    public function handle(CreateMaintenanceRecordCommand $command): MaintenanceRecordDTO
    {
        $vehicle = $this->vehicles->findById($command->vehicleId);

        if (null === $vehicle) {
            throw new VehicleNotFoundException("Vehicle {$command->vehicleId} not found");
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

            if ($invoice->getVehicle()->getId() !== $command->vehicleId) {
                throw new InvoiceNotBelongingToVehicleException("Invoice {$command->invoiceId} does not belong to vehicle {$command->vehicleId}");
            }
        }

        $record = new MaintenanceRecord(
            vehicle: $vehicle,
            createdBy: $this->userReferences->reference($command->userId),
            serviceDate: $command->serviceDate,
            maintenanceRecordType: $type,
            invoice: $invoice,
        );
        $record->setMileage($command->mileage);
        $record->setNotes($command->notes);
        $record->setCost($command->cost);
        $record->setShopName($command->shopName);
        $record->setNextServiceDate($command->nextServiceDate);

        $this->maintenanceRecords->save($record);

        $this->recordMileageSnapshot($vehicle, $command->mileage, $command->serviceDate);

        return MaintenanceRecordDTO::fromMaintenanceRecord($record);
    }

    /**
     * Feeds the Vehicle mileage history from a maintenance record. Vehicle exposes the write
     * as a port, so this context calls it directly instead of publishing a domain event: the
     * project has no event bus yet, and this handler already depends on VehicleRepository.
     *
     * Only readings that move the odometer forward are snapshotted; a lower or equal mileage
     * is ignored rather than rejected, so back-dated maintenance records stay creatable.
     */
    private function recordMileageSnapshot(Vehicle $vehicle, ?int $mileage, \DateTimeImmutable $serviceDate): void
    {
        if (null === $mileage) {
            return;
        }

        $latest = $this->mileageRecords->findLatestByVehicle($vehicle->getId());

        if (null !== $latest && $mileage <= $latest->getMileage()) {
            return;
        }

        $this->mileageRecords->save(MileageRecord::record(
            vehicle: $vehicle,
            mileage: $mileage,
            recordedAt: $serviceDate,
            source: MileageSource::MaintenanceRecord,
            previous: $latest,
        ));
    }
}
