<?php

declare(strict_types=1);

namespace App\Vehicle\Infrastructure\Persistence;

use App\Vehicle\Domain\VehicleUser;
use App\Vehicle\Domain\VehicleUserRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineVehicleUserRepository implements VehicleUserRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(VehicleUser $vehicleUser): void
    {
        $this->entityManager->persist($vehicleUser);
        $this->entityManager->flush();
    }

    public function remove(VehicleUser $vehicleUser): void
    {
        $this->entityManager->remove($vehicleUser);
        $this->entityManager->flush();
    }

    public function findByVehicleAndUser(string $vehicleId, string $userId): ?VehicleUser
    {
        return $this->entityManager->getRepository(VehicleUser::class)->findOneBy([
            'vehicle' => $vehicleId,
            'userId' => $userId,
        ]);
    }

    public function findByVehicle(string $vehicleId): array
    {
        return $this->entityManager->getRepository(VehicleUser::class)->findBy(['vehicle' => $vehicleId]);
    }

    public function findByUser(string $userId): array
    {
        return $this->entityManager->getRepository(VehicleUser::class)->findBy(['userId' => $userId]);
    }
}
