<?php

declare(strict_types=1);

namespace App\Vehicle\Application\AddMileageRecord;

use App\Vehicle\Application\MileageRecordDTO;
use App\Vehicle\Domain\Exception\VehicleNotFoundException;
use App\Vehicle\Domain\MileageRecord;
use App\Vehicle\Domain\MileageRecordRepository;
use App\Vehicle\Domain\MileageSource;
use App\Vehicle\Domain\VehicleRepository;

final readonly class AddMileageRecordHandler
{
    public function __construct(
        private VehicleRepository $vehicles,
        private MileageRecordRepository $mileageRecords,
    ) {
    }

    public function handle(AddMileageRecordCommand $command): MileageRecordDTO
    {
        $vehicle = $this->vehicles->findById($command->vehicleId);

        if (null === $vehicle) {
            throw new VehicleNotFoundException("Vehicle {$command->vehicleId} not found");
        }

        $record = MileageRecord::record(
            vehicle: $vehicle,
            mileage: $command->mileage,
            recordedAt: $command->recordedAt,
            source: MileageSource::Manual,
            previous: $this->mileageRecords->findLatestByVehicle($command->vehicleId),
        );

        $this->mileageRecords->save($record);

        return MileageRecordDTO::fromMileageRecord($record);
    }
}
