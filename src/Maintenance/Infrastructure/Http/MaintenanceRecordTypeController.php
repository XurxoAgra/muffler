<?php

declare(strict_types=1);

namespace App\Maintenance\Infrastructure\Http;

use App\Maintenance\Application\ListMaintenanceRecordTypes\ListMaintenanceRecordTypesHandler;
use App\Maintenance\Application\ListMaintenanceRecordTypes\ListMaintenanceRecordTypesQuery;
use App\Maintenance\Application\ListMaintenanceRecordTypes\MaintenanceRecordTypeDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/maintenance-record-types')]
final class MaintenanceRecordTypeController extends AbstractController
{
    public function __construct(private readonly ListMaintenanceRecordTypesHandler $listTypes)
    {
    }

    #[Route('', name: 'maintenance_record_types.list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $types = $this->listTypes->handle(new ListMaintenanceRecordTypesQuery());

        return $this->json(array_map(
            static fn (MaintenanceRecordTypeDTO $dto) => [
                'id' => $dto->id,
                'key' => $dto->key,
                'icon' => $dto->icon,
                'defaultPeriodicityMonths' => $dto->defaultPeriodicityMonths,
                'defaultPeriodicityKm' => $dto->defaultPeriodicityKm,
                'active' => $dto->active,
            ],
            $types,
        ));
    }
}
