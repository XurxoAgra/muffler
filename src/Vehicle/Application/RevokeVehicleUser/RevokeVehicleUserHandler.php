<?php

declare(strict_types=1);

namespace App\Vehicle\Application\RevokeVehicleUser;

use App\Vehicle\Domain\Exception\CannotRevokeOwnerException;
use App\Vehicle\Domain\Exception\VehicleAccessDeniedException;
use App\Vehicle\Domain\Exception\VehicleNotFoundException;
use App\Vehicle\Domain\Exception\VehicleUserNotFoundException;
use App\Vehicle\Domain\VehicleRepository;
use App\Vehicle\Domain\VehicleUserRepository;
use App\Vehicle\Domain\VehicleUserRole;

final readonly class RevokeVehicleUserHandler
{
    public function __construct(
        private VehicleRepository $vehicles,
        private VehicleUserRepository $vehicleUsers,
    ) {
    }

    public function handle(RevokeVehicleUserCommand $command): void
    {
        $vehicle = $this->vehicles->findById($command->vehicleId);

        if (null === $vehicle) {
            throw new VehicleNotFoundException("Vehicle {$command->vehicleId} not found");
        }

        $targetLink = $this->vehicleUsers->findByVehicleAndUser($vehicle->getId(), $command->targetUserId);

        if (null === $targetLink) {
            throw new VehicleUserNotFoundException("User {$command->targetUserId} has no access to vehicle {$command->vehicleId}");
        }

        if (VehicleUserRole::Owner === $targetLink->getRole()) {
            throw new CannotRevokeOwnerException("The owner of vehicle {$command->vehicleId} cannot be revoked");
        }

        if ($command->revokedByUserId !== $command->targetUserId) {
            $revokerLink = $this->vehicleUsers->findByVehicleAndUser($vehicle->getId(), $command->revokedByUserId);

            if (null === $revokerLink || VehicleUserRole::Owner !== $revokerLink->getRole()) {
                throw new VehicleAccessDeniedException("User {$command->revokedByUserId} cannot revoke user {$command->targetUserId} from vehicle {$command->vehicleId}");
            }
        }

        $this->vehicleUsers->remove($targetLink);
    }
}
