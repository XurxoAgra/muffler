<?php

declare(strict_types=1);

namespace App\Vehicle\Domain;

interface VehicleRepository
{
    public function save(Vehicle $vehicle): void;

    public function findById(string $id): ?Vehicle;

    public function remove(Vehicle $vehicle): void;
}
