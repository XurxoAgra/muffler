<?php

declare(strict_types=1);

namespace App\Vehicle\Application\ListVehicleModels;

final class ListVehicleModelsQuery
{
    public function __construct(public readonly string $makeId)
    {
    }
}
