<?php

declare(strict_types=1);

namespace App\Vehicle\Infrastructure\Http;

use App\Auth\Infrastructure\Security\SymfonyUserAdapter;
use App\Vehicle\Application\CreateVehicle\CreateVehicleCommand;
use App\Vehicle\Application\CreateVehicle\CreateVehicleHandler;
use App\Vehicle\Application\DeleteVehicle\DeleteVehicleCommand;
use App\Vehicle\Application\DeleteVehicle\DeleteVehicleHandler;
use App\Vehicle\Application\GetVehicle\GetVehicleHandler;
use App\Vehicle\Application\GetVehicle\GetVehicleQuery;
use App\Vehicle\Application\ListVehicles\ListVehiclesHandler;
use App\Vehicle\Application\ListVehicles\ListVehiclesQuery;
use App\Vehicle\Application\UpdateVehicle\UpdateVehicleCommand;
use App\Vehicle\Application\UpdateVehicle\UpdateVehicleHandler;
use App\Vehicle\Application\VehicleDTO;
use App\Vehicle\Infrastructure\Http\Request\VehicleRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/vehicles')]
final class VehicleController extends AbstractController
{
    public function __construct(
        private readonly ListVehiclesHandler $listVehicles,
        private readonly GetVehicleHandler $getVehicle,
        private readonly CreateVehicleHandler $createVehicle,
        private readonly UpdateVehicleHandler $updateVehicle,
        private readonly DeleteVehicleHandler $deleteVehicle,
    ) {
    }

    #[Route('', name: 'vehicles.list', methods: ['GET'])]
    public function list(#[CurrentUser] SymfonyUserAdapter $authUser): JsonResponse
    {
        $vehicles = $this->listVehicles->handle(new ListVehiclesQuery($authUser->userId));

        return $this->json(array_map($this->serialize(...), $vehicles));
    }

    #[Route('/{id}', name: 'vehicles.get', methods: ['GET'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function get(string $id, #[CurrentUser] SymfonyUserAdapter $authUser): JsonResponse
    {
        $vehicle = $this->getVehicle->handle(new GetVehicleQuery($id, $authUser->userId));

        return $this->json($this->serialize($vehicle));
    }

    #[Route('', name: 'vehicles.create', methods: ['POST'])]
    public function create(VehicleRequest $request, #[CurrentUser] SymfonyUserAdapter $authUser): JsonResponse
    {
        $vehicle = $this->createVehicle->handle(new CreateVehicleCommand(
            userId: $authUser->userId,
            plate: $request->plate,
            year: $request->year,
            type: $request->type,
            vin: $request->vin,
            makeId: $request->makeId,
            modelId: $request->modelId,
            customMake: $request->customMake,
            customModel: $request->customModel,
        ));

        return $this->json($this->serialize($vehicle), 201);
    }

    #[Route('/{id}', name: 'vehicles.update', methods: ['PUT'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function update(string $id, VehicleRequest $request, #[CurrentUser] SymfonyUserAdapter $authUser): JsonResponse
    {
        $vehicle = $this->updateVehicle->handle(new UpdateVehicleCommand(
            vehicleId: $id,
            userId: $authUser->userId,
            plate: $request->plate,
            year: $request->year,
            type: $request->type,
            vin: $request->vin,
            makeId: $request->makeId,
            modelId: $request->modelId,
            customMake: $request->customMake,
            customModel: $request->customModel,
        ));

        return $this->json($this->serialize($vehicle));
    }

    #[Route('/{id}', name: 'vehicles.delete', methods: ['DELETE'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function delete(string $id, #[CurrentUser] SymfonyUserAdapter $authUser): JsonResponse
    {
        $this->deleteVehicle->handle(new DeleteVehicleCommand($id, $authUser->userId));

        return new JsonResponse(null, 204);
    }

    private function serialize(VehicleDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'plate' => $dto->plate,
            'year' => $dto->year,
            'type' => $dto->type,
            'vin' => $dto->vin,
            'make' => $dto->make,
            'model' => $dto->model,
            'custom_make' => $dto->customMake,
            'custom_model' => $dto->customModel,
            'role' => $dto->role,
        ];
    }
}
