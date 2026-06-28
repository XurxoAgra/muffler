<?php

declare(strict_types=1);

namespace App\Maintenance\Application\ScanInvoice;

use App\Maintenance\Application\InvoiceDTO;
use App\Maintenance\Application\Port\FileUploaderInterface;
use App\Maintenance\Application\Port\OcrServiceInterface;
use App\Maintenance\Application\Port\UserReferenceProvider;
use App\Maintenance\Domain\Invoice;
use App\Maintenance\Domain\InvoiceRepository;
use App\Vehicle\Domain\Exception\VehicleNotFoundException;
use App\Vehicle\Domain\VehicleRepository;

final readonly class ScanInvoiceHandler
{
    public function __construct(
        private VehicleRepository $vehicles,
        private InvoiceRepository $invoices,
        private FileUploaderInterface $fileUploader,
        private OcrServiceInterface $ocrService,
        private UserReferenceProvider $userReferences,
    ) {
    }

    public function handle(ScanInvoiceCommand $command): ScanInvoiceResult
    {
        $vehicle = $this->vehicles->findById($command->vehicleId);

        if ($vehicle === null) {
            throw new VehicleNotFoundException("Vehicle {$command->vehicleId} not found");
        }

        $filePath = $this->fileUploader->upload($command->file, $command->vehicleId);

        $invoice = new Invoice(
            vehicle: $vehicle,
            uploadedBy: $this->userReferences->reference($command->userId),
            filePath: $filePath,
        );

        $this->invoices->save($invoice);

        $ocrSuggestion = $this->ocrService->extractInvoiceData($filePath);

        return new ScanInvoiceResult(InvoiceDTO::fromInvoice($invoice), $ocrSuggestion);
    }
}
