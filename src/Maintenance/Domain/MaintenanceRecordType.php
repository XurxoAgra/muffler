<?php

declare(strict_types=1);

namespace App\Maintenance\Domain;

use Symfony\Component\Uid\Uuid;

final class MaintenanceRecordType
{
    private Uuid $id;

    public function __construct(
        private string $key,
        private ?string $icon = null,
        private ?int $defaultPeriodicityMonths = null,
        private ?int $defaultPeriodicityKm = null,
        private bool $active = true,
    ) {
        $this->id = Uuid::v7();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getDefaultPeriodicityMonths(): ?int
    {
        return $this->defaultPeriodicityMonths;
    }

    public function getDefaultPeriodicityKm(): ?int
    {
        return $this->defaultPeriodicityKm;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
