<?php

declare(strict_types=1);

namespace App\Vehicle\Infrastructure\Import;

use App\Vehicle\Application\ImportVehicleCatalog\VehicleCatalogFileReader;
use App\Vehicle\Application\ImportVehicleCatalog\VehicleCatalogRow;

final class FileVehicleCatalogReader implements VehicleCatalogFileReader
{
    public function read(string $filePath): array
    {
        if (!is_readable($filePath)) {
            throw new \InvalidArgumentException(sprintf('El fichero "%s" no existe o no se puede leer.', $filePath));
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'json' => $this->readJson($filePath),
            'csv' => $this->readCsv($filePath),
            default => throw new \InvalidArgumentException(sprintf('Formato de fichero no soportado: ".%s". Se esperaba .json o .csv.', $extension)),
        };
    }

    /** @return VehicleCatalogRow[] */
    private function readJson(string $filePath): array
    {
        $contents = file_get_contents($filePath);
        if (false === $contents) {
            throw new \InvalidArgumentException(sprintf('No se pudo leer el fichero "%s".', $filePath));
        }

        try {
            $decoded = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \InvalidArgumentException(sprintf('El fichero "%s" no contiene JSON válido: %s', $filePath, $exception->getMessage()), previous: $exception);
        }

        if (!is_array($decoded)) {
            throw new \InvalidArgumentException(sprintf('El fichero "%s" debe contener un array de registros en la raíz.', $filePath));
        }

        $rows = [];
        foreach ($decoded as $entry) {
            if (!is_array($entry) || !isset($entry['marca'], $entry['modelo'])) {
                throw new \InvalidArgumentException(sprintf('El fichero "%s" contiene un registro sin las columnas "marca"/"modelo".', $filePath));
            }
            $rows[] = new VehicleCatalogRow((string) $entry['marca'], (string) $entry['modelo']);
        }

        return $rows;
    }

    /** @return VehicleCatalogRow[] */
    private function readCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (false === $handle) {
            throw new \InvalidArgumentException(sprintf('No se pudo leer el fichero "%s".', $filePath));
        }

        try {
            $header = fgetcsv($handle);
            if (false === $header) {
                throw new \InvalidArgumentException(sprintf('El fichero "%s" está vacío o no tiene cabecera.', $filePath));
            }

            $makeIndex = array_search('marca', $header, true);
            $modelIndex = array_search('modelo', $header, true);
            if (false === $makeIndex || false === $modelIndex) {
                throw new \InvalidArgumentException(sprintf('El fichero "%s" debe tener las columnas "marca" y "modelo" en la cabecera.', $filePath));
            }

            $rows = [];
            while (false !== ($line = fgetcsv($handle))) {
                if (!isset($line[$makeIndex], $line[$modelIndex])) {
                    throw new \InvalidArgumentException(sprintf('El fichero "%s" contiene una fila con menos columnas que la cabecera.', $filePath));
                }
                $rows[] = new VehicleCatalogRow((string) $line[$makeIndex], (string) $line[$modelIndex]);
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }
}
