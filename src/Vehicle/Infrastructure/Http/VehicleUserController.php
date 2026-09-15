<?php

declare(strict_types=1);

namespace App\Vehicle\Infrastructure\Http;

use App\Auth\Infrastructure\Security\SymfonyUserAdapter;
use App\Vehicle\Application\InviteUserToVehicle\InviteUserToVehicleCommand;
use App\Vehicle\Application\InviteUserToVehicle\InviteUserToVehicleHandler;
use App\Vehicle\Application\ListVehicleUsers\ListVehicleUsersHandler;
use App\Vehicle\Application\ListVehicleUsers\ListVehicleUsersQuery;
use App\Vehicle\Application\RevokeVehicleUser\RevokeVehicleUserCommand;
use App\Vehicle\Application\RevokeVehicleUser\RevokeVehicleUserHandler;
use App\Vehicle\Application\VehicleUserDTO;
use App\Vehicle\Domain\Exception\VehicleNotFoundException;
use App\Vehicle\Domain\Vehicle;
use App\Vehicle\Domain\VehicleRepository;
use App\Vehicle\Infrastructure\Http\Request\InviteUserRequest;
use App\Vehicle\Infrastructure\Security\VehicleVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/vehicles')]
final class VehicleUserController extends AbstractController
{
    private const UUID_REQUIREMENT = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';

    public function __construct(
        private readonly VehicleRepository $vehicles,
        private readonly ListVehicleUsersHandler $listVehicleUsers,
        private readonly InviteUserToVehicleHandler $inviteUserToVehicle,
        private readonly RevokeVehicleUserHandler $revokeVehicleUser,
    ) {
    }

    #[Route('/{vehicleId}/users', name: 'vehicle_users.list', methods: ['GET'], requirements: ['vehicleId' => self::UUID_REQUIREMENT])]
    public function list(string $vehicleId, #[CurrentUser] SymfonyUserAdapter $authUser): JsonResponse
    {
        $this->denyAccessUnlessGranted(VehicleVoter::VIEW, $this->findVehicleOrFail($vehicleId));

        $users = $this->listVehicleUsers->handle(new ListVehicleUsersQuery($vehicleId, $authUser->userId));

        return $this->json(array_map($this->serialize(...), $users));
    }

    #[Route('/{vehicleId}/users', name: 'vehicle_users.invite', methods: ['POST'], requirements: ['vehicleId' => self::UUID_REQUIREMENT])]
    public function invite(string $vehicleId, InviteUserRequest $request, #[CurrentUser] SymfonyUserAdapter $authUser): JsonResponse
    {
        $this->denyAccessUnlessGranted(VehicleVoter::MANAGE_USERS, $this->findVehicleOrFail($vehicleId));

        $vehicleUser = $this->inviteUserToVehicle->handle(new InviteUserToVehicleCommand(
            vehicleId: $vehicleId,
            invitedByUserId: $authUser->userId,
            invitedUserEmail: $request->email,
        ));

        return $this->json($this->serialize($vehicleUser), 201);
    }

    #[Route('/{vehicleId}/users/{userId}', name: 'vehicle_users.revoke', methods: ['DELETE'], requirements: ['vehicleId' => self::UUID_REQUIREMENT, 'userId' => self::UUID_REQUIREMENT])]
    public function revoke(string $vehicleId, string $userId, #[CurrentUser] SymfonyUserAdapter $authUser): JsonResponse
    {
        $this->denyAccessUnlessGranted(VehicleVoter::VIEW, $this->findVehicleOrFail($vehicleId));

        $this->revokeVehicleUser->handle(new RevokeVehicleUserCommand(
            vehicleId: $vehicleId,
            revokedByUserId: $authUser->userId,
            targetUserId: $userId,
        ));

        return new JsonResponse(null, 204);
    }

    private function findVehicleOrFail(string $id): Vehicle
    {
        $vehicle = $this->vehicles->findById($id);

        if (null === $vehicle) {
            throw new VehicleNotFoundException("Vehicle {$id} not found");
        }

        return $vehicle;
    }

    private function serialize(VehicleUserDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'userId' => $dto->userId,
            'email' => $dto->email,
            'firstName' => $dto->firstName,
            'lastName' => $dto->lastName,
            'role' => $dto->role,
        ];
    }
}
