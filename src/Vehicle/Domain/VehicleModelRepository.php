<?php

declare(strict_types=1);

namespace App\Vehicle\Domain;

interface VehicleModelRepository
{
    /** @return VehicleModel[] */
    public function findByMakeOrderedByName(string $makeId): array;

    public function findById(string $id): ?VehicleModel;

    public function findOneByMakeAndName(string $makeId, string $name): ?VehicleModel;

    public function add(VehicleModel $model): void;
}
