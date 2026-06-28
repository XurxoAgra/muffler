<?php

declare(strict_types=1);

namespace App\Vehicle\Domain;

use Symfony\Component\Uid\Uuid;

final class VehicleModel
{
    private string $id;

    public function __construct(
        private VehicleMake $make,
        private string $name,
    ) {
        $this->id = Uuid::v7()->toRfc4122();
    }

    public function getId(): string
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
