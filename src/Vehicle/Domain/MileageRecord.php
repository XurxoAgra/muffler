<?php

declare(strict_types=1);

namespace App\Vehicle\Domain;

use App\Vehicle\Domain\Exception\InvalidMileageException;
use App\Vehicle\Domain\Exception\MileageRegressionException;
use Symfony\Component\Uid\Uuid;

/**
 * Immutable odometer snapshot of a vehicle. Snapshots are append-only: there is no
 * setter, a wrong reading is corrected by recording a new one.
 */
final class MileageRecord
{
    private Uuid $id;

    private function __construct(
        private Vehicle $vehicle,
        private int $mileage,
        private \DateTimeImmutable $recordedAt,
        private MileageSource $source,
    ) {
        $this->id = Uuid::v7();
    }

    /**
     * @param self|null $previous the latest snapshot already known for the vehicle, if any
     */
    public static function record(
        Vehicle $vehicle,
        int $mileage,
        \DateTimeImmutable $recordedAt,
        MileageSource $source,
        ?self $previous = null,
    ): self {
        if ($mileage < 0) {
            throw new InvalidMileageException('Mileage cannot be negative');
        }

        if (null !== $previous && $mileage < $previous->getMileage()) {
            throw new MileageRegressionException("Mileage {$mileage} is lower than the last recorded mileage {$previous->getMileage()}");
        }

        return new self($vehicle, $mileage, $recordedAt, $source);
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getVehicle(): Vehicle
    {
        return $this->vehicle;
    }

    public function getMileage(): int
    {
        return $this->mileage;
    }

    public function getRecordedAt(): \DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function getSource(): MileageSource
    {
        return $this->source;
    }
}
