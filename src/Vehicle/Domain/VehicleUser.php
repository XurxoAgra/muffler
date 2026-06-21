<?php

declare(strict_types=1);

namespace App\Vehicle\Domain;

use Symfony\Component\Uid\Uuid;

final class VehicleUser
{
    private string $id;

    public function __construct(
        private Vehicle $vehicle,
        private string $userId,
        private VehicleUserRole $role,
    ) {
        $this->id = Uuid::v7()->toRfc4122();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getVehicle(): Vehicle
    {
        return $this->vehicle;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getRole(): VehicleUserRole
    {
        return $this->role;
    }

    public function setRole(VehicleUserRole $role): void
    {
        $this->role = $role;
    }
}
