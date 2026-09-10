<?php

declare(strict_types=1);

namespace App\Maintenance\Domain;

use App\Auth\Domain\User\User;
use App\Vehicle\Domain\Vehicle;
use Symfony\Component\Uid\Uuid;

final class MaintenanceRecord
{
    private Uuid $id;

    private ?int $mileage = null;

    private ?string $notes = null;

    private ?string $cost = null;

    private ?string $shopName = null;

    private ?\DateTimeImmutable $nextServiceDate = null;

    private bool $verified = false;

    private \DateTimeImmutable $createdAt;

    public function __construct(
        private Vehicle $vehicle,
        private User $createdBy,
        private \DateTimeImmutable $serviceDate,
        private MaintenanceRecordType $maintenanceRecordType,
        private ?Invoice $invoice = null,
    ) {
        $this->id = Uuid::v7();
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

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): void
    {
        $this->invoice = $invoice;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function getServiceDate(): \DateTimeImmutable
    {
        return $this->serviceDate;
    }

    public function setServiceDate(\DateTimeImmutable $serviceDate): void
    {
        $this->serviceDate = $serviceDate;
    }

    public function getMileage(): ?int
    {
        return $this->mileage;
    }

    public function setMileage(?int $mileage): void
    {
        $this->mileage = $mileage;
    }

    public function getMaintenanceRecordType(): MaintenanceRecordType
    {
        return $this->maintenanceRecordType;
    }

    public function setMaintenanceRecordType(MaintenanceRecordType $maintenanceRecordType): void
    {
        $this->maintenanceRecordType = $maintenanceRecordType;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    public function getCost(): ?string
    {
        return $this->cost;
    }

    public function setCost(?string $cost): void
    {
        $this->cost = $cost;
    }

    public function getShopName(): ?string
    {
        return $this->shopName;
    }

    public function setShopName(?string $shopName): void
    {
        $this->shopName = $shopName;
    }

    public function getNextServiceDate(): ?\DateTimeImmutable
    {
        return $this->nextServiceDate;
    }

    public function setNextServiceDate(?\DateTimeImmutable $nextServiceDate): void
    {
        $this->nextServiceDate = $nextServiceDate;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
