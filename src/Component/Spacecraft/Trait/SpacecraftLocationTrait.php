<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\MapRegionInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

trait SpacecraftLocationTrait
{
    use SpacecraftTrait;

    public function getPosX(): int
    {
        return $this->getThis()->getLocation()->getX();
    }

    public function getPosY(): int
    {
        return $this->getThis()->getLocation()->getY();
    }

    public function getMap(): ?MapInterface
    {
        $location = $this->getThis()->getLocation();

        if ($location instanceof MapInterface) {
            return $location;
        }

        return $location->getSystem()->getMap();
    }

    public function getStarsystemMap(): ?StarSystemMapInterface
    {
        if ($this->getThis()->getLocation() instanceof StarSystemMapInterface) {
            return $this->getThis()->getLocation();
        }

        return null;
    }

    public function getLayer(): ?LayerInterface
    {
        return $this->getThis()->getLocation()->getLayer();
    }

    public function getMapRegion(): ?MapRegionInterface
    {
        $systemMap = $this->getStarsystemMap();
        if ($systemMap !== null) {
            return null;
        }

        $map = $this->getMap();
        if ($map === null) {
            return null;
        }

        return $map->getMapRegion();
    }

    public function isOverColony(): ?ColonyInterface
    {
        return $this->getStarsystemMap() !== null ? $this->getStarsystemMap()->getColony() : null;
    }

    public function isOverSystem(): ?StarSystemInterface
    {
        $location = $this->getThis()->getLocation();
        if ($location instanceof StarSystemMapInterface) {
            return null;
        }

        return $location->getSystem();
    }

    public function getSystem(): ?StarSystemInterface
    {
        return $this->getStarsystemMap() !== null ? $this->getStarsystemMap()->getSystem() : null;
    }

    public function getSectorString(): string
    {
        return $this->getThis()->getLocation()->getSectorString();
    }
}
