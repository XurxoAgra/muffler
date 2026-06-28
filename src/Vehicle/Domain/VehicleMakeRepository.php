<?php

declare(strict_types=1);

namespace App\Vehicle\Domain;

interface VehicleMakeRepository
{
    /** @return VehicleMake[] */
    public function findAllOrderedByName(): array;

    public function findById(string $id): ?VehicleMake;

    public function findOneByName(string $name): ?VehicleMake;

    public function add(VehicleMake $make): void;

    public function flush(): void;
}
