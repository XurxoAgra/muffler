<?php

declare(strict_types=1);

namespace App\Vehicle\Infrastructure\Persistence;

use App\Vehicle\Domain\VehicleModel;
use App\Vehicle\Domain\VehicleModelRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineVehicleModelRepository implements VehicleModelRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findByMakeOrderedByName(string $makeId): array
    {
        return $this->entityManager->getRepository(VehicleModel::class)->findBy(['make' => $makeId], ['name' => 'ASC']);
    }

    public function findById(string $id): ?VehicleModel
    {
        return $this->entityManager->find(VehicleModel::class, $id);
    }
}
