<?php

declare(strict_types=1);

namespace App\Vehicle\Infrastructure\Persistence;

use App\Vehicle\Domain\Vehicle;
use App\Vehicle\Domain\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineVehicleRepository implements VehicleRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(Vehicle $vehicle): void
    {
        $this->entityManager->persist($vehicle);
        $this->entityManager->flush();
    }

    public function findById(string $id): ?Vehicle
    {
        return $this->entityManager->find(Vehicle::class, $id);
    }

    public function remove(Vehicle $vehicle): void
    {
        $this->entityManager->remove($vehicle);
        $this->entityManager->flush();
    }
}
