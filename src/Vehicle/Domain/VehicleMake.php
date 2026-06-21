<?php

declare(strict_types=1);

namespace App\Vehicle\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class VehicleMake
{
    private ?int $id = null;

    /** @var Collection<int, VehicleModel> */
    private Collection $models;

    public function __construct(private string $name)
    {
        $this->models = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /** @return Collection<int, VehicleModel> */
    public function getModels(): Collection
    {
        return $this->models;
    }

    public function addModel(VehicleModel $model): void
    {
        if (!$this->models->contains($model)) {
            $this->models->add($model);
        }
    }

    public function removeModel(VehicleModel $model): void
    {
        $this->models->removeElement($model);
    }
}
