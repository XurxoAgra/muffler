<?php

declare(strict_types=1);

namespace App\Vehicle\Application\ListMileageHistory;

use App\Vehicle\Application\MileageRecordDTO;
use App\Vehicle\Domain\MileageRecord;
use App\Vehicle\Domain\MileageRecordRepository;

final readonly class ListMileageHistoryHandler
{
    public function __construct(private MileageRecordRepository $mileageRecords)
    {
    }

    /** @return MileageRecordDTO[] */
    public function handle(ListMileageHistoryQuery $query): array
    {
        $records = $this->mileageRecords->findByVehicleOrderedByRecordedAtDesc($query->vehicleId);

        return array_map(
            static fn (MileageRecord $record) => MileageRecordDTO::fromMileageRecord($record),
            $records,
        );
    }
}
