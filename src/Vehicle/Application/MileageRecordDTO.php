<?php

declare(strict_types=1);

namespace App\Vehicle\Application;

use App\Vehicle\Domain\MileageRecord;

final class MileageRecordDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $vehicleId,
        public readonly int $mileage,
        public readonly string $recordedAt,
        public readonly string $source,
    ) {
    }

    public static function fromMileageRecord(MileageRecord $record): self
    {
        return new self(
            id: $record->getId()->toRfc4122(),
            vehicleId: $record->getVehicle()->getId(),
            mileage: $record->getMileage(),
            recordedAt: $record->getRecordedAt()->format(DATE_ATOM),
            source: $record->getSource()->value,
        );
    }
}
