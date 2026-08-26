<?php

declare(strict_types=1);

namespace App\Vehicle\Domain;

enum VehicleType: string
{
    case Car = 'car';
    case Moto = 'moto';
    case Van = 'van';
    case Truck = 'truck';

    public function label(): string
    {
        return match ($this) {
            self::Car => 'Coche',
            self::Moto => 'Moto',
            self::Van => 'Furgoneta',
            self::Truck => 'Camión',
        };
    }
}
