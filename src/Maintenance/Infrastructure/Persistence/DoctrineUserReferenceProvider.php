<?php

declare(strict_types=1);

namespace App\Maintenance\Infrastructure\Persistence;

use App\Auth\Domain\User\User;
use App\Auth\Domain\User\UserId;
use App\Maintenance\Application\Port\UserReferenceProvider;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineUserReferenceProvider implements UserReferenceProvider
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function reference(string $userId): User
    {
        return $this->entityManager->getReference(User::class, UserId::fromString($userId));
    }
}
