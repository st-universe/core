<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Trait;

use Doctrine\ORM\Mapping\OneToOne;
use Stu\Orm\Entity\SpacecraftAssimilated;

trait SpacecraftAssimilationTrait
{
    #[OneToOne(targetEntity: SpacecraftAssimilated::class, mappedBy: 'spacecraft')]
    private ?SpacecraftAssimilated $assimilation = null;

    public function getAssimilation(): ?SpacecraftAssimilated
    {
        return $this->assimilation;
    }

    public function setAssimilation(?SpacecraftAssimilated $assimilation): self
    {
        $this->assimilation = $assimilation;
        return $this;
    }

    public function isBeingAssimilated(): bool
    {
        return $this->assimilation?->isActive() === true;
    }

    public function getAssimilationProgress(): float
    {
        return ($this->assimilation?->getProgressAt(time()) ?? 0) / 100;
    }
}
