<?php

declare(strict_types=1);

namespace App\Maintenance\Application\Port;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileUploaderInterface
{
    /**
     * Stores the file and returns its path relative to the storage root.
     */
    public function upload(UploadedFile $file, string $vehicleId): string;
}
