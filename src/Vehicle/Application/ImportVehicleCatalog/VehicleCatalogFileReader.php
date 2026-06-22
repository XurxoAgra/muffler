<?php

declare(strict_types=1);

namespace App\Vehicle\Application\ImportVehicleCatalog;

interface VehicleCatalogFileReader
{
    /**
     * @return VehicleCatalogRow[]
     *
     * @throws \InvalidArgumentException if the file extension is unsupported or the content is malformed
     */
    public function read(string $filePath): array;
}
