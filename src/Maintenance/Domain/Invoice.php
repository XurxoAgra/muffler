<?php

declare(strict_types=1);

namespace App\Maintenance\Domain;

use App\Auth\Domain\User\User;
use App\Vehicle\Domain\Vehicle;
use Symfony\Component\Uid\Uuid;

final class Invoice
{
    private Uuid $id;

    private ?string $amount = null;

    private ?\DateTimeImmutable $date = null;

    private ?string $shopName = null;

    private ?string $description = null;

    private InvoiceStatus $status;

    private \DateTimeImmutable $createdAt;

    public function __construct(
        private Vehicle $vehicle,
        private User $uploadedBy,
        private string $filePath,
    ) {
        $this->id = Uuid::v7();
        $this->status = InvoiceStatus::Pending;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getVehicle(): Vehicle
    {
        return $this->vehicle;
    }

    public function getUploadedBy(): User
    {
        return $this->uploadedBy;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function getShopName(): ?string
    {
        return $this->shopName;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatus(): InvoiceStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
