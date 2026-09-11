<?php

declare(strict_types=1);

namespace App\Vehicle\Application\ListMileageHistory;

final class ListMileageHistoryQuery
{
    public function __construct(public readonly string $vehicleId)
    {
    }
}
