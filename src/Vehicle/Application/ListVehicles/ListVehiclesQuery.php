<?php

declare(strict_types=1);

namespace App\Vehicle\Application\ListVehicles;

final class ListVehiclesQuery
{
    public function __construct(public readonly string $userId)
    {
    }
}
