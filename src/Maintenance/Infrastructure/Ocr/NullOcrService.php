<?php

declare(strict_types=1);

namespace App\Maintenance\Infrastructure\Ocr;

use App\Maintenance\Application\Port\InvoiceOcrResult;
use App\Maintenance\Application\Port\OcrServiceInterface;

// Stub implementation: always returns an empty suggestion. The real OCR
// integration replaces this in a later phase.
final class NullOcrService implements OcrServiceInterface
{
    public function extractInvoiceData(string $filePath): InvoiceOcrResult
    {
        return new InvoiceOcrResult();
    }
}
