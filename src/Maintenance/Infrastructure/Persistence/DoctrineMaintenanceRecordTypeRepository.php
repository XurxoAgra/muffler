<?php

declare(strict_types=1);

namespace App\Maintenance\Infrastructure\Persistence;

use App\Maintenance\Domain\MaintenanceRecordType;
use App\Maintenance\Domain\MaintenanceRecordTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class DoctrineMaintenanceRecordTypeRepository implements MaintenanceRecordTypeRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findAllOrderedByKey(): array
    {
        return $this->entityManager->getRepository(MaintenanceRecordType::class)->findBy([], ['key' => 'ASC']);
    }

    public function findById(string $id): ?MaintenanceRecordType
    {
        if (!Uuid::isValid($id)) {
            return null;
        }

        return $this->entityManager->find(MaintenanceRecordType::class, Uuid::fromString($id));
    }
}
