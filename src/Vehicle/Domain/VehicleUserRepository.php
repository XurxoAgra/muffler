<?php

declare(strict_types=1);

namespace App\Vehicle\Domain;

interface VehicleUserRepository
{
    public function save(VehicleUser $vehicleUser): void;

    public function remove(VehicleUser $vehicleUser): void;

    public function findByVehicleAndUser(string $vehicleId, string $userId): ?VehicleUser;

    /** @return VehicleUser[] */
    public function findByVehicle(string $vehicleId): array;

    /** @return VehicleUser[] */
    public function findByUser(string $userId): array;
}
