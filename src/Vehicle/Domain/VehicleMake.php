<?php

declare(strict_types=1);

namespace App\Vehicle\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

final class VehicleMake
{
    private string $id;

    /** @var Collection<int, VehicleModel> */
    private Collection $models;

    public function __construct(private string $name)
    {
        $this->id = Uuid::v7()->toRfc4122();
        $this->models = new ArrayCollection();
    }

    public function getId(): string
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
