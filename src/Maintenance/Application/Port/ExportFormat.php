<?php

declare(strict_types=1);

namespace App\Maintenance\Application\Port;

enum ExportFormat: string
{
    case Pdf = 'pdf';
    case Csv = 'csv';

    public function mimeType(): string
    {
        return match ($this) {
            self::Pdf => 'application/pdf',
            self::Csv => 'text/csv',
        };
    }
}
