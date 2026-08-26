<?php

declare(strict_types=1);

namespace App\Maintenance\Infrastructure\Http;

use App\Auth\Infrastructure\Security\SymfonyUserAdapter;
use App\Maintenance\Application\InvoiceDTO;
use App\Maintenance\Application\Port\InvoiceOcrResult;
use App\Maintenance\Application\ScanInvoice\ScanInvoiceCommand;
use App\Maintenance\Application\ScanInvoice\ScanInvoiceHandler;
use App\Shared\Application\Exception\ValidationException;
use App\Vehicle\Domain\Exception\VehicleNotFoundException;
use App\Vehicle\Domain\Vehicle;
use App\Vehicle\Domain\VehicleRepository;
use App\Vehicle\Infrastructure\Security\VehicleVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api')]
final class InvoiceController extends AbstractController
{
    private const UUID_REQUIREMENT = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';

    public function __construct(
        private readonly VehicleRepository $vehicles,
        private readonly ScanInvoiceHandler $scanInvoice,
    ) {
    }

    #[Route('/vehicles/{vehicleId}/invoices/scan', name: 'invoices.scan', methods: ['POST'], requirements: ['vehicleId' => self::UUID_REQUIREMENT])]
    public function scan(string $vehicleId, Request $request, #[CurrentUser] SymfonyUserAdapter $authUser): JsonResponse
    {
        $this->denyAccessUnlessGranted(VehicleVoter::EDIT, $this->findVehicleOrFail($vehicleId));

        $file = $request->files->get('file');

        if (null === $file) {
            throw new ValidationException('Invalid input', ['file' => ['A file is required']]);
        }

        $result = $this->scanInvoice->handle(new ScanInvoiceCommand(
            vehicleId: $vehicleId,
            userId: $authUser->userId,
            file: $file,
        ));

        return $this->json([
            'invoice' => $this->serializeInvoice($result->invoice),
            'ocrSuggestion' => $this->serializeOcrSuggestion($result->ocrSuggestion),
        ], 201);
    }

    private function findVehicleOrFail(string $id): Vehicle
    {
        $vehicle = $this->vehicles->findById($id);

        if (null === $vehicle) {
            throw new VehicleNotFoundException("Vehicle {$id} not found");
        }

        return $vehicle;
    }

    private function serializeInvoice(InvoiceDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'vehicleId' => $dto->vehicleId,
            'uploadedById' => $dto->uploadedById,
            'filePath' => $dto->filePath,
            'amount' => $dto->amount,
            'date' => $dto->date,
            'shopName' => $dto->shopName,
            'description' => $dto->description,
            'status' => $dto->status,
            'createdAt' => $dto->createdAt,
        ];
    }

    private function serializeOcrSuggestion(InvoiceOcrResult $result): array
    {
        return [
            'amount' => $result->amount,
            'date' => $result->date,
            'shopName' => $result->shopName,
            'suggestedType' => $result->suggestedType,
            'suggestedNotes' => $result->suggestedNotes,
        ];
    }
}
