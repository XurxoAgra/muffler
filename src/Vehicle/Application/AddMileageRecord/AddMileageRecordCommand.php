<?php

declare(strict_types=1);

namespace App\Vehicle\Application\AddMileageRecord;

final class AddMileageRecordCommand
{
    public function __construct(
        public readonly string $vehicleId,
        public readonly int $mileage,
        public readonly \DateTimeImmutable $recordedAt,
    ) {
    }
}
