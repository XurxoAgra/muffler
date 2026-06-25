<?php

declare(strict_types=1);

namespace App\Maintenance\Application\Port;

interface OcrServiceInterface
{
    public function extractInvoiceData(string $filePath): InvoiceOcrResult;
}
