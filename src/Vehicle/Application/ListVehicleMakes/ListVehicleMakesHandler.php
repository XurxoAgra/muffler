<?php

declare(strict_types=1);

namespace App\Vehicle\Application\ListVehicleMakes;

use App\Vehicle\Domain\VehicleMakeRepository;

final readonly class ListVehicleMakesHandler
{
    public function __construct(private VehicleMakeRepository $makes)
    {
    }

    /** @return VehicleMakeDTO[] */
    public function handle(ListVehicleMakesQuery $query): array
    {
        return array_map(
            VehicleMakeDTO::fromVehicleMake(...),
            $this->makes->findAllOrderedByName(),
        );
    }
}
