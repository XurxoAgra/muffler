<?php

declare(strict_types=1);

namespace App\Maintenance\Application\ListMaintenanceRecordTypes;

use App\Maintenance\Domain\MaintenanceRecordType;

final class MaintenanceRecordTypeDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $key,
        public readonly ?string $icon,
        public readonly ?int $defaultPeriodicityMonths,
        public readonly ?int $defaultPeriodicityKm,
        public readonly bool $active,
    ) {
    }

    public static function fromMaintenanceRecordType(MaintenanceRecordType $type): self
    {
        return new self(
            id: $type->getId()->toRfc4122(),
            key: $type->getKey(),
            icon: $type->getIcon(),
            defaultPeriodicityMonths: $type->getDefaultPeriodicityMonths(),
            defaultPeriodicityKm: $type->getDefaultPeriodicityKm(),
            active: $type->isActive(),
        );
    }
}
