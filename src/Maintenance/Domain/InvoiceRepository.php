<?php

declare(strict_types=1);

namespace App\Maintenance\Domain;

interface InvoiceRepository
{
    public function save(Invoice $invoice): void;

    public function findById(string $id): ?Invoice;
}
