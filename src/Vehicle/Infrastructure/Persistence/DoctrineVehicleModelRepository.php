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

    public function findOneByMakeAndName(string $makeId, string $name): ?VehicleModel
    {
        return $this->entityManager->createQueryBuilder()
            ->select('model')
            ->from(VehicleModel::class, 'model')
            ->where('model.make = :makeId')
            ->andWhere('LOWER(model.name) = LOWER(:name)')
            ->setParameter('makeId', $makeId)
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function add(VehicleModel $model): void
    {
        $this->entityManager->persist($model);
    }
}
