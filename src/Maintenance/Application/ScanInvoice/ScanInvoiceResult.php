<?php

declare(strict_types=1);

namespace App\Maintenance\Application\ScanInvoice;

use App\Maintenance\Application\InvoiceDTO;
use App\Maintenance\Application\Port\InvoiceOcrResult;

final class ScanInvoiceResult
{
    public function __construct(
        public readonly InvoiceDTO $invoice,
        public readonly InvoiceOcrResult $ocrSuggestion,
    ) {
    }
}
