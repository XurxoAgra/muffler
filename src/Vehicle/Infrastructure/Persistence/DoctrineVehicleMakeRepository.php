<?php

declare(strict_types=1);

namespace App\Vehicle\Infrastructure\Persistence;

use App\Vehicle\Domain\VehicleMake;
use App\Vehicle\Domain\VehicleMakeRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineVehicleMakeRepository implements VehicleMakeRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findAllOrderedByName(): array
    {
        return $this->entityManager->getRepository(VehicleMake::class)->findBy([], ['name' => 'ASC']);
    }

    public function findById(string $id): ?VehicleMake
    {
        return $this->entityManager->find(VehicleMake::class, $id);
    }
}
