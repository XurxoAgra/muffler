<?php

declare(strict_types=1);

namespace App\Maintenance\Infrastructure\Storage;

use App\Maintenance\Application\Port\FileUploaderInterface;
use App\Shared\Application\Exception\ValidationException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

final class LocalFileUploader implements FileUploaderInterface
{
    private const ALLOWED_MIME_TYPES = ['application/pdf', 'image/jpeg', 'image/png'];

    private const MAX_SIZE_BYTES = 10 * 1024 * 1024;

    public function __construct(
        #[Autowire('%kernel.project_dir%/var/storage/invoices')]
        private readonly string $storageDirectory,
    ) {
    }

    public function upload(UploadedFile $file, string $vehicleId): string
    {
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            throw new ValidationException('Invalid input', ['file' => ['Only PDF, JPG and PNG files are allowed']]);
        }

        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            throw new ValidationException('Invalid input', ['file' => ['File exceeds the maximum allowed size of 10 MB']]);
        }

        $targetDirectory = $this->storageDirectory.'/'.$vehicleId;
        $filename = Uuid::v7()->toRfc4122().'.'.$file->guessExtension();

        (new Filesystem())->mkdir($targetDirectory);
        $file->move($targetDirectory, $filename);

        return $vehicleId.'/'.$filename;
    }
}
