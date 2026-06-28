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

    public function findOneByName(string $name): ?VehicleMake
    {
        return $this->entityManager->createQueryBuilder()
            ->select('make')
            ->from(VehicleMake::class, 'make')
            ->where('LOWER(make.name) = LOWER(:name)')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function add(VehicleMake $make): void
    {
        $this->entityManager->persist($make);
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
