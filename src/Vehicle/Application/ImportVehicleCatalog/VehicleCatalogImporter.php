<?php

declare(strict_types=1);

namespace App\Vehicle\Application\ImportVehicleCatalog;

use App\Vehicle\Domain\VehicleMake;
use App\Vehicle\Domain\VehicleMakeRepository;
use App\Vehicle\Domain\VehicleModel;
use App\Vehicle\Domain\VehicleModelRepository;

final readonly class VehicleCatalogImporter
{
    private const DEFAULT_BATCH_SIZE = 100;

    public function __construct(
        private VehicleMakeRepository $makes,
        private VehicleModelRepository $models,
        private VehicleCatalogFileReader $reader,
    ) {
    }

    /**
     * Parses the file and reduces it to the unique (make, model) pairs it contains.
     *
     * @return VehicleCatalogRow[]
     *
     * @throws \InvalidArgumentException if the file extension is unsupported or the content is malformed
     */
    public function prepareRows(string $filePath): array
    {
        $seen = [];
        $uniqueRows = [];

        foreach ($this->reader->read($filePath) as $row) {
            $make = trim($row->make);
            $model = trim($row->model);

            if ('' === $make || '' === $model) {
                continue;
            }

            $key = mb_strtolower($make).'|'.mb_strtolower($model);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $uniqueRows[] = new VehicleCatalogRow($make, $model);
        }

        return $uniqueRows;
    }

    /**
     * Persists the given rows, creating makes/models that don't already exist (case-insensitively).
     * Flushes every $batchSize new entities to keep the unit of work small for large catalogs.
     *
     * @param VehicleCatalogRow[] $rows
     */
    public function import(array $rows, ?callable $onRowProcessed = null, int $batchSize = self::DEFAULT_BATCH_SIZE): VehicleCatalogImportResult
    {
        $makesCreated = 0;
        $makesExisting = 0;
        $modelsCreated = 0;
        $modelsExisting = 0;
        $pendingFlush = 0;

        /** @var array<string, VehicleMake> $makeCache */
        $makeCache = [];

        foreach ($rows as $row) {
            $makeKey = mb_strtolower($row->make);

            if (isset($makeCache[$makeKey])) {
                $make = $makeCache[$makeKey];
            } else {
                $make = $this->makes->findOneByName($row->make);
                if (null !== $make) {
                    ++$makesExisting;
                } else {
                    $make = new VehicleMake($row->make);
                    $this->makes->add($make);
                    ++$makesCreated;
                    ++$pendingFlush;
                }
                $makeCache[$makeKey] = $make;
            }

            $model = $this->models->findOneByMakeAndName($make->getId(), $row->model);
            if (null !== $model) {
                ++$modelsExisting;
            } else {
                $model = new VehicleModel($make, $row->model);
                $this->models->add($model);
                ++$modelsCreated;
                ++$pendingFlush;
            }

            if ($pendingFlush >= $batchSize) {
                $this->makes->flush();
                $pendingFlush = 0;
            }

            if (null !== $onRowProcessed) {
                $onRowProcessed();
            }
        }

        $this->makes->flush();

        return new VehicleCatalogImportResult($makesCreated, $makesExisting, $modelsCreated, $modelsExisting);
    }
}
