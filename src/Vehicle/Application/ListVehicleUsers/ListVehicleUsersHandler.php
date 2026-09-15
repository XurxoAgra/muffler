<?php

declare(strict_types=1);

namespace App\Vehicle\Application\ListVehicleUsers;

use App\Vehicle\Application\Port\UserFinder;
use App\Vehicle\Application\Port\UserSummary;
use App\Vehicle\Application\VehicleUserDTO;
use App\Vehicle\Domain\Exception\VehicleAccessDeniedException;
use App\Vehicle\Domain\Exception\VehicleNotFoundException;
use App\Vehicle\Domain\VehicleRepository;
use App\Vehicle\Domain\VehicleUser;
use App\Vehicle\Domain\VehicleUserRepository;

final readonly class ListVehicleUsersHandler
{
    public function __construct(
        private VehicleRepository $vehicles,
        private VehicleUserRepository $vehicleUsers,
        private UserFinder $users,
    ) {
    }

    /** @return VehicleUserDTO[] */
    public function handle(ListVehicleUsersQuery $query): array
    {
        $vehicle = $this->vehicles->findById($query->vehicleId);

        if (null === $vehicle) {
            throw new VehicleNotFoundException("Vehicle {$query->vehicleId} not found");
        }

        $requesterLink = $this->vehicleUsers->findByVehicleAndUser($vehicle->getId(), $query->requestingUserId);

        if (null === $requesterLink) {
            throw new VehicleAccessDeniedException("User {$query->requestingUserId} has no access to vehicle {$query->vehicleId}");
        }

        $links = $this->vehicleUsers->findByVehicle($vehicle->getId());

        return array_map(
            fn (VehicleUser $link): VehicleUserDTO => VehicleUserDTO::fromVehicleUser($link, $this->resolveUser($link->getUserId())),
            $links,
        );
    }

    private function resolveUser(string $userId): UserSummary
    {
        $user = $this->users->findById($userId);

        if (null === $user) {
            throw new \RuntimeException("Linked user {$userId} could not be resolved");
        }

        return $user;
    }
}
