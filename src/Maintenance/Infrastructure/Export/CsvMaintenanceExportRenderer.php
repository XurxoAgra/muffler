<?php

declare(strict_types=1);

namespace App\Maintenance\Infrastructure\Export;

use App\Maintenance\Application\Port\MaintenanceExport;
use App\Maintenance\Application\Port\MaintenanceExportRecord;
use App\Maintenance\Application\Port\VehicleSummary;

final readonly class CsvMaintenanceExportRenderer
{
    private const SEPARATOR = ',';
    private const ENCLOSURE = '"';
    private const ESCAPE = '';
    private const EOL = "\r\n";

    // Without a BOM, Excel reads the file as ANSI and mangles accented shop names and notes.
    private const UTF8_BOM = "\xEF\xBB\xBF";

    // Spreadsheet apps evaluate cells starting with these as formulas (CSV injection).
    private const FORMULA_PREFIXES = ['=', '+', '-', '@', "\t", "\r"];
    private const FORMULA_NEUTRALIZER = "'";

    private const RECORD_HEADER = ['Service date', 'Type', 'Mileage (km)', 'Cost', 'Shop', 'Next service date', 'Verified', 'Notes'];

    private const YES = 'yes';
    private const NO = 'no';

    public function render(MaintenanceExport $export): string
    {
        $handle = fopen('php://temp', 'r+');

        if (false === $handle) {
            throw new \RuntimeException('Could not open a temporary stream for the CSV export');
        }

        try {
            fwrite($handle, self::UTF8_BOM);

            // Vehicle metadata goes first as key/value rows, separated from the records table by an empty row.
            foreach ($this->vehicleRows($export->vehicle) as $row) {
                $this->writeRow($handle, $row);
            }
            $this->writeRow($handle, []);

            $this->writeRow($handle, self::RECORD_HEADER);
            foreach ($export->records as $record) {
                $this->writeRow($handle, $this->recordRow($record));
            }

            rewind($handle);

            return (string) stream_get_contents($handle);
        } finally {
            fclose($handle);
        }
    }

    /** @return list<list<string|int|null>> */
    private function vehicleRows(VehicleSummary $vehicle): array
    {
        $rows = [
            ['Plate', $vehicle->plate],
            ['Make', $vehicle->make],
            ['Model', $vehicle->model],
            ['Year', $vehicle->year],
        ];

        if (null !== $vehicle->vin) {
            $rows[] = ['VIN', $vehicle->vin];
        }

        return $rows;
    }

    /** @return list<string|int|null> */
    private function recordRow(MaintenanceExportRecord $record): array
    {
        return [
            $record->serviceDate,
            $record->type,
            $record->mileage,
            $record->cost,
            $record->shopName,
            $record->nextServiceDate,
            $record->verified ? self::YES : self::NO,
            $record->notes,
        ];
    }

    /**
     * @param resource              $handle
     * @param list<string|int|null> $row
     */
    private function writeRow($handle, array $row): void
    {
        fputcsv(
            $handle,
            array_map($this->neutralizeFormula(...), $row),
            self::SEPARATOR,
            self::ENCLOSURE,
            self::ESCAPE,
            self::EOL,
        );
    }

    private function neutralizeFormula(string|int|null $value): string
    {
        $value = (string) $value;

        if ('' !== $value && in_array($value[0], self::FORMULA_PREFIXES, true)) {
            return self::FORMULA_NEUTRALIZER.$value;
        }

        return $value;
    }
}
