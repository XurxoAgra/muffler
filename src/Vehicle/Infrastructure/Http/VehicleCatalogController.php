<?php

declare(strict_types=1);

namespace App\Vehicle\Infrastructure\Http;

use App\Vehicle\Application\ListVehicleMakes\ListVehicleMakesHandler;
use App\Vehicle\Application\ListVehicleMakes\ListVehicleMakesQuery;
use App\Vehicle\Application\ListVehicleMakes\VehicleMakeDTO;
use App\Vehicle\Application\ListVehicleModels\ListVehicleModelsHandler;
use App\Vehicle\Application\ListVehicleModels\ListVehicleModelsQuery;
use App\Vehicle\Application\ListVehicleModels\VehicleModelDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/vehicle-makes')]
final class VehicleCatalogController extends AbstractController
{
    public function __construct(
        private readonly ListVehicleMakesHandler $listMakes,
        private readonly ListVehicleModelsHandler $listModels,
    ) {
    }

    #[Route('', name: 'vehicle_makes.list', methods: ['GET'])]
    public function makes(): JsonResponse
    {
        $makes = $this->listMakes->handle(new ListVehicleMakesQuery());

        return $this->json(array_map(
            static fn (VehicleMakeDTO $dto) => ['id' => $dto->id, 'name' => $dto->name],
            $makes,
        ));
    }

    #[Route('/{id}/models', name: 'vehicle_makes.models', methods: ['GET'], requirements: ['id' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'])]
    public function models(string $id): JsonResponse
    {
        $models = $this->listModels->handle(new ListVehicleModelsQuery($id));

        return $this->json(array_map(
            static fn (VehicleModelDTO $dto) => ['id' => $dto->id, 'name' => $dto->name],
            $models,
        ));
    }
}
