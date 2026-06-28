<?php

declare(strict_types=1);

namespace App\Vehicle\Domain;

use Symfony\Component\Uid\Uuid;

final class Vehicle
{
    private string $id;

    private ?string $vin = null;

    private ?VehicleMake $make = null;

    private ?VehicleModel $model = null;

    private ?string $customMake = null;

    private ?string $customModel = null;

    public function __construct(
        private string $plate,
        private int $year,
        private VehicleType $type,
        private string $ownerId,
    ) {
        $this->id = Uuid::v7()->toRfc4122();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPlate(): string
    {
        return $this->plate;
    }

    public function setPlate(string $plate): void
    {
        $this->plate = $plate;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    public function getType(): VehicleType
    {
        return $this->type;
    }

    public function setType(VehicleType $type): void
    {
        $this->type = $type;
    }

    public function getOwnerId(): string
    {
        return $this->ownerId;
    }

    public function setOwnerId(string $ownerId): void
    {
        $this->ownerId = $ownerId;
    }

    public function getVin(): ?string
    {
        return $this->vin;
    }

    public function setVin(?string $vin): void
    {
        $this->vin = $vin;
    }

    public function getMake(): ?VehicleMake
    {
        return $this->make;
    }

    public function setMake(?VehicleMake $make): void
    {
        $this->make = $make;
    }

    public function getModel(): ?VehicleModel
    {
        return $this->model;
    }

    public function setModel(?VehicleModel $model): void
    {
        $this->model = $model;
    }

    public function getCustomMake(): ?string
    {
        return $this->customMake;
    }

    public function setCustomMake(?string $customMake): void
    {
        $this->customMake = $customMake;
    }

    public function getCustomModel(): ?string
    {
        return $this->customModel;
    }

    public function setCustomModel(?string $customModel): void
    {
        $this->customModel = $customModel;
    }
}
