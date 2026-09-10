<?php

declare(strict_types=1);

namespace App\Maintenance\Application\ListMaintenanceRecordTypes;

use App\Maintenance\Domain\MaintenanceRecordTypeRepository;

final readonly class ListMaintenanceRecordTypesHandler
{
    public function __construct(private MaintenanceRecordTypeRepository $types)
    {
    }

    /** @return MaintenanceRecordTypeDTO[] */
    public function handle(ListMaintenanceRecordTypesQuery $query): array
    {
        return array_map(
            MaintenanceRecordTypeDTO::fromMaintenanceRecordType(...),
            $this->types->findAllOrderedByKey(),
        );
    }
}
