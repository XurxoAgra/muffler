<?php

declare(strict_types=1);

namespace App\Maintenance\Infrastructure\Http;

use App\Auth\Infrastructure\Security\SymfonyUserAdapter;
use App\Maintenance\Application\CreateMaintenanceRecord\CreateMaintenanceRecordCommand;
use App\Maintenance\Application\CreateMaintenanceRecord\CreateMaintenanceRecordHandler;
use App\Maintenance\Application\DeleteMaintenanceRecord\DeleteMaintenanceRecordCommand;
use App\Maintenance\Application\DeleteMaintenanceRecord\DeleteMaintenanceRecordHandler;
use App\Maintenance\Application\GetMaintenanceRecord\GetMaintenanceRecordHandler;
use App\Maintenance\Application\GetMaintenanceRecord\GetMaintenanceRecordQuery;
use App\Maintenance\Application\ListMaintenanceRecords\ListMaintenanceRecordsHandler;
use App\Maintenance\Application\ListMaintenanceRecords\ListMaintenanceRecordsQuery;
use App\Maintenance\Application\MaintenanceRecordDTO;
use App\Maintenance\Application\UpdateMaintenanceRecord\UpdateMaintenanceRecordCommand;
use App\Maintenance\Application\UpdateMaintenanceRecord\UpdateMaintenanceRecordHandler;
use App\Maintenance\Domain\Exception\MaintenanceRecordNotFoundException;
use App\Maintenance\Domain\MaintenanceRecord;
use App\Maintenance\Domain\MaintenanceRecordRepository;
use App\Maintenance\Infrastructure\Http\Request\MaintenanceRecordRequest;
use App\Vehicle\Domain\Exception\VehicleNotFoundException;
use App\Vehicle\Domain\Vehicle;
use App\Vehicle\Domain\VehicleRepository;
use App\Vehicle\Infrastructure\Security\VehicleVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api')]
final class MaintenanceRecordController extends AbstractController
{
    private const UUID_REQUIREMENT = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';

    public function __construct(
        private readonly VehicleRepository $vehicles,
        private readonly MaintenanceRecordRepository $maintenanceRecords,
        private readonly ListMaintenanceRecordsHandler $listMaintenanceRecords,
        private readonly GetMaintenanceRecordHandler $getMaintenanceRecord,
        private readonly CreateMaintenanceRecordHandler $createMaintenanceRecord,
        private readonly UpdateMaintenanceRecordHandler $updateMaintenanceRecord,
        private readonly DeleteMaintenanceRecordHandler $deleteMaintenanceRecord,
    ) {
    }

    #[Route('/vehicles/{vehicleId}/maintenance-records', name: 'maintenance_records.list', methods: ['GET'], requirements: ['vehicleId' => self::UUID_REQUIREMENT])]
    public function list(string $vehicleId): JsonResponse
    {
        $this->denyAccessUnlessGranted(VehicleVoter::VIEW, $this->findVehicleOrFail($vehicleId));

        $records = $this->listMaintenanceRecords->handle(new ListMaintenanceRecordsQuery($vehicleId));

        return $this->json(array_map($this->serialize(...), $records));
    }

    #[Route('/maintenance-records/{id}', name: 'maintenance_records.get', methods: ['GET'], requirements: ['id' => self::UUID_REQUIREMENT])]
    public function get(string $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(VehicleVoter::VIEW, $this->findRecordOrFail($id)->getVehicle());

        $record = $this->getMaintenanceRecord->handle(new GetMaintenanceRecordQuery($id));

        return $this->json($this->serialize($record));
    }

    #[Route('/vehicles/{vehicleId}/maintenance-records', name: 'maintenance_records.create', methods: ['POST'], requirements: ['vehicleId' => self::UUID_REQUIREMENT])]
    public function create(string $vehicleId, MaintenanceRecordRequest $request, #[CurrentUser] SymfonyUserAdapter $authUser): JsonResponse
    {
        $this->denyAccessUnlessGranted(VehicleVoter::EDIT, $this->findVehicleOrFail($vehicleId));

        $record = $this->createMaintenanceRecord->handle(new CreateMaintenanceRecordCommand(
            vehicleId: $vehicleId,
            userId: $authUser->userId,
            serviceDate: $request->serviceDate,
            type: $request->type,
            mileage: $request->mileage,
            notes: $request->notes,
            cost: $request->cost,
            shopName: $request->shopName,
            nextServiceDate: $request->nextServiceDate,
            invoiceId: $request->invoiceId,
        ));

        return $this->json($this->serialize($record), 201);
    }

    #[Route('/maintenance-records/{id}', name: 'maintenance_records.update', methods: ['PUT'], requirements: ['id' => self::UUID_REQUIREMENT])]
    public function update(string $id, MaintenanceRecordRequest $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(VehicleVoter::EDIT, $this->findRecordOrFail($id)->getVehicle());

        $record = $this->updateMaintenanceRecord->handle(new UpdateMaintenanceRecordCommand(
            maintenanceRecordId: $id,
            serviceDate: $request->serviceDate,
            type: $request->type,
            mileage: $request->mileage,
            notes: $request->notes,
            cost: $request->cost,
            shopName: $request->shopName,
            nextServiceDate: $request->nextServiceDate,
            invoiceId: $request->invoiceId,
        ));

        return $this->json($this->serialize($record));
    }

    #[Route('/maintenance-records/{id}', name: 'maintenance_records.delete', methods: ['DELETE'], requirements: ['id' => self::UUID_REQUIREMENT])]
    public function delete(string $id): JsonResponse
    {
        $this->denyAccessUnlessGranted(VehicleVoter::EDIT, $this->findRecordOrFail($id)->getVehicle());

        $this->deleteMaintenanceRecord->handle(new DeleteMaintenanceRecordCommand($id));

        return new JsonResponse(null, 204);
    }

    private function findVehicleOrFail(string $id): Vehicle
    {
        $vehicle = $this->vehicles->findById($id);

        if (null === $vehicle) {
            throw new VehicleNotFoundException("Vehicle {$id} not found");
        }

        return $vehicle;
    }

    private function findRecordOrFail(string $id): MaintenanceRecord
    {
        $record = $this->maintenanceRecords->findById($id);

        if (null === $record) {
            throw new MaintenanceRecordNotFoundException("Maintenance record {$id} not found");
        }

        return $record;
    }

    private function serialize(MaintenanceRecordDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'vehicleId' => $dto->vehicleId,
            'invoiceId' => $dto->invoiceId,
            'createdById' => $dto->createdById,
            'serviceDate' => $dto->serviceDate,
            'mileage' => $dto->mileage,
            'type' => $dto->type,
            'notes' => $dto->notes,
            'cost' => $dto->cost,
            'shopName' => $dto->shopName,
            'nextServiceDate' => $dto->nextServiceDate,
            'verified' => $dto->verified,
            'createdAt' => $dto->createdAt,
        ];
    }
}
