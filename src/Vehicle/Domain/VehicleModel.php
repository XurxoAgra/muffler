<?php

declare(strict_types=1);

namespace App\Vehicle\Domain;

final class VehicleModel
{
    private ?int $id = null;

    public function __construct(
        private VehicleMake $make,
        private string $name,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMake(): VehicleMake
    {
        return $this->make;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
