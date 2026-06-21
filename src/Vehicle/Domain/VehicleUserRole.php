<?php

declare(strict_types=1);

namespace App\Vehicle\Domain;

enum VehicleUserRole: string
{
    case Owner = 'owner';
    case Shared = 'shared';
}
