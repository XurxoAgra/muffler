<?php

declare(strict_types=1);

namespace App\Vehicle\Application;

use App\Shared\Application\Exception\ValidationException;
use App\Vehicle\Domain\VehicleMake;
use App\Vehicle\Domain\VehicleMakeRepository;
use App\Vehicle\Domain\VehicleModel;
use App\Vehicle\Domain\VehicleModelRepository;

final readonly class VehicleCatalogResolver
{
    public function __construct(
        private VehicleMakeRepository $makes,
        private VehicleModelRepository $models,
    ) {
    }

    /** @return array{0: ?VehicleMake, 1: ?VehicleModel} */
    public function resolve(?string $makeId, ?string $modelId): array
    {
        $make = null;
        $model = null;

        if ($makeId !== null) {
            $make = $this->makes->findById($makeId);

            if ($make === null) {
                throw new ValidationException('Invalid input', ['makeId' => ['Make not found']]);
            }
        }

        if ($modelId !== null) {
            $model = $this->models->findById($modelId);

            if ($model === null) {
                throw new ValidationException('Invalid input', ['modelId' => ['Model not found']]);
            }

            if ($makeId === null || $model->getMake()->getId() !== $makeId) {
                throw new ValidationException('Invalid input', ['modelId' => ['Model does not belong to the specified make']]);
            }
        }

        return [$make, $model];
    }
}
