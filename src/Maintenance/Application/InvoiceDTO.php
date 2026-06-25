<?php

declare(strict_types=1);

namespace App\Maintenance\Application;

use App\Maintenance\Domain\Invoice;

final class InvoiceDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $vehicleId,
        public readonly string $uploadedById,
        public readonly string $filePath,
        public readonly ?string $amount,
        public readonly ?string $date,
        public readonly ?string $shopName,
        public readonly ?string $description,
        public readonly string $status,
        public readonly string $createdAt,
    ) {
    }

    public static function fromInvoice(Invoice $invoice): self
    {
        return new self(
            id: $invoice->getId()->toRfc4122(),
            vehicleId: $invoice->getVehicle()->getId(),
            uploadedById: $invoice->getUploadedBy()->id()->value(),
            filePath: $invoice->getFilePath(),
            amount: $invoice->getAmount(),
            date: $invoice->getDate()?->format('Y-m-d'),
            shopName: $invoice->getShopName(),
            description: $invoice->getDescription(),
            status: $invoice->getStatus()->value,
            createdAt: $invoice->getCreatedAt()->format(DATE_ATOM),
        );
    }
}
