<?php

declare(strict_types=1);

namespace App\Maintenance\Application\ExportVehicleMaintenance;

use App\Maintenance\Application\Port\ExportFormat;
use App\Maintenance\Application\Port\MaintenanceExport;
use App\Maintenance\Application\Port\MaintenanceExportRecord;
use App\Maintenance\Application\Port\MaintenanceExportRendererInterface;
use App\Maintenance\Application\Port\VehicleFinder;
use App\Maintenance\Application\Port\VehicleSummary;
use App\Maintenance\Domain\MaintenanceRecord;
use App\Maintenance\Domain\MaintenanceRecordRepository;
use App\Vehicle\Domain\Exception\VehicleNotFoundException;

final readonly class ExportVehicleMaintenanceHandler
{
    private const DATE_FORMAT = 'Y-m-d';
    private const FILENAME_PREFIX = 'maintenance-history';
    private const COST_DECIMALS = 2;
    private const CENTS_PER_UNIT = 100;

    public function __construct(
        private VehicleFinder $vehicles,
        private MaintenanceRecordRepository $maintenanceRecords,
        private MaintenanceExportRendererInterface $renderer,
    ) {
    }

    public function handle(ExportVehicleMaintenanceQuery $query): ExportVehicleMaintenanceResult
    {
        $vehicle = $this->vehicles->findById($query->vehicleId);

        if (null === $vehicle) {
            throw new VehicleNotFoundException("Vehicle {$query->vehicleId} not found");
        }

        $records = array_map(
            fn (MaintenanceRecord $record) => $this->toExportRecord($record, $query->typeLabels),
            $this->maintenanceRecords->findByVehicleOrderedByServiceDateDesc($query->vehicleId),
        );

        $export = new MaintenanceExport($vehicle, $records, $this->totalCost($records));

        return new ExportVehicleMaintenanceResult(
            content: $this->renderer->render($query->format, $export),
            mimeType: $query->format->mimeType(),
            filename: $this->filename($vehicle, $query->format),
        );
    }

    /** @param array<string, string> $typeLabels */
    private function toExportRecord(MaintenanceRecord $record, array $typeLabels): MaintenanceExportRecord
    {
        $typeKey = $record->getMaintenanceRecordType()->getKey();

        return new MaintenanceExportRecord(
            serviceDate: $record->getServiceDate()->format(self::DATE_FORMAT),
            type: $typeLabels[$typeKey] ?? $typeKey,
            mileage: $record->getMileage(),
            cost: $record->getCost(),
            shopName: $record->getShopName(),
            nextServiceDate: $record->getNextServiceDate()?->format(self::DATE_FORMAT),
            verified: $record->isVerified(),
            notes: $record->getNotes(),
        );
    }

    /**
     * Sums in integer cents: costs are DECIMAL(10,2) strings, and float addition would drift.
     *
     * @param MaintenanceExportRecord[] $records
     */
    private function totalCost(array $records): ?string
    {
        $costs = array_filter(array_column($records, 'cost'), static fn (?string $cost) => null !== $cost);

        if ([] === $costs) {
            return null;
        }

        $cents = array_sum(array_map($this->toCents(...), $costs));

        return sprintf('%d.%02d', intdiv($cents, self::CENTS_PER_UNIT), $cents % self::CENTS_PER_UNIT);
    }

    private function toCents(string $amount): int
    {
        [$units, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        return (int) $units * self::CENTS_PER_UNIT
            + (int) str_pad(substr($fraction, 0, self::COST_DECIMALS), self::COST_DECIMALS, '0');
    }

    private function filename(VehicleSummary $vehicle, ExportFormat $format): string
    {
        $plate = trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $vehicle->plate), '-');
        $basename = '' === $plate ? self::FILENAME_PREFIX : self::FILENAME_PREFIX.'-'.$plate;

        return $basename.'.'.$format->value;
    }
}
