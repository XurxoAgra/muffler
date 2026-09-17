<?php

declare(strict_types=1);

namespace App\Maintenance\Infrastructure\Export;

use App\Maintenance\Application\Port\ExportFormat;
use App\Maintenance\Application\Port\MaintenanceExport;
use App\Maintenance\Application\Port\MaintenanceExportRendererInterface;

/**
 * One renderer per format, picked with an exhaustive match rather than a tagged-service locator:
 * two formats don't justify the indirection, and a new ExportFormat case without a renderer
 * fails here with an UnhandledMatchError instead of at a service lookup.
 */
final readonly class DispatchingMaintenanceExportRenderer implements MaintenanceExportRendererInterface
{
    public function __construct(
        private PdfMaintenanceExportRenderer $pdf,
        private CsvMaintenanceExportRenderer $csv,
    ) {
    }

    public function render(ExportFormat $format, MaintenanceExport $export): string
    {
        return match ($format) {
            ExportFormat::Pdf => $this->pdf->render($export),
            ExportFormat::Csv => $this->csv->render($export),
        };
    }
}
