<?php

declare(strict_types=1);

namespace App\Vehicle\Application\InviteUserToVehicle;

use App\Vehicle\Application\Port\UserFinder;
use App\Vehicle\Application\VehicleUserDTO;
use App\Vehicle\Domain\Exception\InvitedUserNotFoundException;
use App\Vehicle\Domain\Exception\VehicleAccessDeniedException;
use App\Vehicle\Domain\Exception\VehicleNotFoundException;
use App\Vehicle\Domain\Exception\VehicleUserAlreadyExistsException;
use App\Vehicle\Domain\VehicleRepository;
use App\Vehicle\Domain\VehicleUser;
use App\Vehicle\Domain\VehicleUserRepository;
use App\Vehicle\Domain\VehicleUserRole;

final readonly class InviteUserToVehicleHandler
{
    public function __construct(
        private VehicleRepository $vehicles,
        private VehicleUserRepository $vehicleUsers,
        private UserFinder $users,
    ) {
    }

    public function handle(InviteUserToVehicleCommand $command): VehicleUserDTO
    {
        $vehicle = $this->vehicles->findById($command->vehicleId);

        if (null === $vehicle) {
            throw new VehicleNotFoundException("Vehicle {$command->vehicleId} not found");
        }

        $inviterLink = $this->vehicleUsers->findByVehicleAndUser($vehicle->getId(), $command->invitedByUserId);

        if (null === $inviterLink || VehicleUserRole::Owner !== $inviterLink->getRole()) {
            throw new VehicleAccessDeniedException("User {$command->invitedByUserId} cannot invite users to vehicle {$command->vehicleId}");
        }

        $invitedUser = $this->users->findByEmail($command->invitedUserEmail);

        if (null === $invitedUser) {
            throw new InvitedUserNotFoundException("No registered user found for email {$command->invitedUserEmail}");
        }

        if (null !== $this->vehicleUsers->findByVehicleAndUser($vehicle->getId(), $invitedUser->userId)) {
            throw new VehicleUserAlreadyExistsException("User {$invitedUser->userId} already has access to vehicle {$command->vehicleId}");
        }

        $vehicleUser = new VehicleUser($vehicle, $invitedUser->userId, VehicleUserRole::Shared);
        $this->vehicleUsers->save($vehicleUser);

        return VehicleUserDTO::fromVehicleUser($vehicleUser, $invitedUser);
    }
}
