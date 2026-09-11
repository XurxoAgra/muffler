<?php

declare(strict_types=1);

namespace App\Vehicle\Infrastructure\Persistence;

use App\Vehicle\Domain\MileageRecord;
use App\Vehicle\Domain\MileageRecordRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineMileageRecordRepository implements MileageRecordRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(MileageRecord $record): void
    {
        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }

    public function findByVehicleOrderedByRecordedAtDesc(string $vehicleId): array
    {
        return $this->entityManager->getRepository(MileageRecord::class)->createQueryBuilder('m')
            ->andWhere('m.vehicle = :vehicleId')
            ->setParameter('vehicleId', $vehicleId)
            ->orderBy('m.recordedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLatestByVehicle(string $vehicleId): ?MileageRecord
    {
        return $this->entityManager->getRepository(MileageRecord::class)->createQueryBuilder('m')
            ->andWhere('m.vehicle = :vehicleId')
            ->setParameter('vehicleId', $vehicleId)
            ->orderBy('m.recordedAt', 'DESC')
            ->addOrderBy('m.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
