<?php

declare(strict_types=1);

namespace App\Maintenance\Application\Port;

interface MaintenanceExportRendererInterface
{
    /**
     * Returns the file contents: binary for PDF, UTF-8 text for CSV.
     */
    public function render(ExportFormat $format, MaintenanceExport $export): string;
}
