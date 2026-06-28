<?php

declare(strict_types=1);

namespace App\Maintenance\Application\Port;

final class InvoiceOcrResult
{
    public function __construct(
        public readonly ?string $amount = null,
        public readonly ?string $date = null,
        public readonly ?string $shopName = null,
        public readonly ?string $suggestedType = null,
        public readonly ?string $suggestedNotes = null,
    ) {
    }
}
