<?php

declare(strict_types=1);

namespace App\Vehicle\Infrastructure\Http;

use App\Vehicle\Application\AddMileageRecord\AddMileageRecordCommand;
use App\Vehicle\Application\AddMileageRecord\AddMileageRecordHandler;
use App\Vehicle\Application\ListMileageHistory\ListMileageHistoryHandler;
use App\Vehicle\Application\ListMileageHistory\ListMileageHistoryQuery;
use App\Vehicle\Application\MileageRecordDTO;
use App\Vehicle\Domain\Exception\VehicleNotFoundException;
use App\Vehicle\Domain\Vehicle;
use App\Vehicle\Domain\VehicleRepository;
use App\Vehicle\Infrastructure\Http\Request\MileageRecordRequest;
use App\Vehicle\Infrastructure\Security\VehicleVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vehicles')]
final class MileageRecordController extends AbstractController
{
    private const UUID_REQUIREMENT = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';

    public function __construct(
        private readonly VehicleRepository $vehicles,
        private readonly ListMileageHistoryHandler $listMileageHistory,
        private readonly AddMileageRecordHandler $addMileageRecord,
    ) {
    }

    #[Route('/{vehicleId}/mileage-records', name: 'mileage_records.list', methods: ['GET'], requirements: ['vehicleId' => self::UUID_REQUIREMENT])]
    public function list(string $vehicleId): JsonResponse
    {
        $this->denyAccessUnlessGranted(VehicleVoter::VIEW, $this->findVehicleOrFail($vehicleId));

        $records = $this->listMileageHistory->handle(new ListMileageHistoryQuery($vehicleId));

        return $this->json(array_map($this->serialize(...), $records));
    }

    #[Route('/{vehicleId}/mileage-records', name: 'mileage_records.create', methods: ['POST'], requirements: ['vehicleId' => self::UUID_REQUIREMENT])]
    public function create(string $vehicleId, MileageRecordRequest $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(VehicleVoter::EDIT, $this->findVehicleOrFail($vehicleId));

        $record = $this->addMileageRecord->handle(new AddMileageRecordCommand(
            vehicleId: $vehicleId,
            mileage: $request->mileage,
            recordedAt: $request->recordedAt,
        ));

        return $this->json($this->serialize($record), 201);
    }

    private function findVehicleOrFail(string $id): Vehicle
    {
        $vehicle = $this->vehicles->findById($id);

        if (null === $vehicle) {
            throw new VehicleNotFoundException("Vehicle {$id} not found");
        }

        return $vehicle;
    }

    private function serialize(MileageRecordDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'vehicleId' => $dto->vehicleId,
            'mileage' => $dto->mileage,
            'recordedAt' => $dto->recordedAt,
            'source' => $dto->source,
        ];
    }
}
