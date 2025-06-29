<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapRegion;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;

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

    public function getMap(): ?Map
    {
        $location = $this->getThis()->getLocation();

        if ($location instanceof Map) {
            return $location;
        }

        return $location->getSystem()->getMap();
    }

    public function getStarsystemMap(): ?StarSystemMap
    {
        if ($this->getThis()->getLocation() instanceof StarSystemMap) {
            return $this->getThis()->getLocation();
        }

        return null;
    }

    public function getLayer(): ?Layer
    {
        return $this->getThis()->getLocation()->getLayer();
    }

    public function getMapRegion(): ?MapRegion
    {
        $systemMap = $this->getStarsystemMap();
        if ($systemMap !== null) {
            return null;
        }

        return $this->getMap()?->getMapRegion();
    }

    public function isOverColony(): ?Colony
    {
        return $this->getStarsystemMap()?->getColony();
    }

    public function isOverSystem(): ?StarSystem
    {
        $location = $this->getThis()->getLocation();
        if ($location instanceof StarSystemMap) {
            return null;
        }

        return $location->getSystem();
    }

    public function getSystem(): ?StarSystem
    {
        return $this->getStarsystemMap()?->getSystem();
    }

    public function getSectorString(): string
    {
        return $this->getThis()->getLocation()->getSectorString();
    }
}
