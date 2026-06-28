<?php

declare(strict_types=1);

namespace App\Maintenance\Application\ScanInvoice;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ScanInvoiceCommand
{
    public function __construct(
        public readonly string $vehicleId,
        public readonly string $userId,
        public readonly UploadedFile $file,
    ) {
    }
}
