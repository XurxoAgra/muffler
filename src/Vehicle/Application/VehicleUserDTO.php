<?php

declare(strict_types=1);

namespace App\Vehicle\Application;

use App\Vehicle\Application\Port\UserSummary;
use App\Vehicle\Domain\VehicleUser;

final class VehicleUserDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $email,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $role,
    ) {
    }

    public static function fromVehicleUser(VehicleUser $vehicleUser, UserSummary $user): self
    {
        return new self(
            id: $vehicleUser->getId(),
            userId: $user->userId,
            email: $user->email,
            firstName: $user->firstName,
            lastName: $user->lastName,
            role: $vehicleUser->getRole()->value,
        );
    }
}
