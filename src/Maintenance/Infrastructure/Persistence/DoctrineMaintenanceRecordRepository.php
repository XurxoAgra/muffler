<?php

declare(strict_types=1);

namespace App\Maintenance\Infrastructure\Persistence;

use App\Maintenance\Domain\MaintenanceRecord;
use App\Maintenance\Domain\MaintenanceRecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class DoctrineMaintenanceRecordRepository implements MaintenanceRecordRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(MaintenanceRecord $record): void
    {
        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function findById(string $id): ?MaintenanceRecord
    {
        return $this->entityManager->find(MaintenanceRecord::class, Uuid::fromString($id));
    }

    public function remove(MaintenanceRecord $record): void
    {
        $this->entityManager->remove($record);
        $this->entityManager->flush();
    }

    public function findByVehicleOrderedByServiceDateDesc(string $vehicleId): array
    {
        return $this->entityManager->getRepository(MaintenanceRecord::class)->createQueryBuilder('r')
            ->andWhere('r.vehicle = :vehicleId')
            ->setParameter('vehicleId', $vehicleId)
            ->orderBy('r.serviceDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
