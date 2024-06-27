<?php

declare(strict_types=1);

namespace Stu\Lib\Map;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ReadableCollection;
use InvalidArgumentException;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

class Location
{
    private MapInterface|StarSystemMapInterface $location;
    private ?MapInterface $parentMap;

    public function __construct(?MapInterface $map, ?StarSystemMapInterface $sysMap)
    {
        if (
            $map === null && $sysMap === null
        ) {
            throw new InvalidArgumentException('At least on of Map or systemMap has to be filled');
        }

        if ($sysMap !== null) {

            if (
                $map === null
                && !$sysMap->getSystem()->isWormhole()
            ) {
                throw new InvalidArgumentException('Map can only be null in Wormholes');
            }

            if ($sysMap->getSystem()->getMapField() !== $map) {
                throw new InvalidArgumentException('System of SystemMap does not belong to current Map Field');
            }
        }

        $this->location = $sysMap ?? $map;
        $this->parentMap = $map;
    }

    public function get(): MapInterface|StarSystemMapInterface
    {
        return $this->location;
    }

    public function getParentMapLocation(): ?Location
    {
        $parentMap = $this->parentMap;
        if ($parentMap === null) {
            return null;
        }

        return new Location($parentMap, null);
    }

    /**
     * @return Collection<int, ShipInterface>
     */
    public function getShips(): Collection
    {
        return $this->location->getShips();
    }

    public function getSectorString(): string
    {
        return $this->location->getSectorString();
    }

    /**
     * @return ReadableCollection<int, AnomalyInterface>
     */
    public function getAnomalies(): ReadableCollection
    {
        return $this->location->getAnomalies()->filter(fn (AnomalyInterface $anomaly): bool => $anomaly->isActive());
    }

    public function hasAnomaly(AnomalyTypeEnum $type): bool
    {
        foreach ($this->getAnomalies() as $anomaly) {
            if ($anomaly->getAnomalyType()->getId() === $type->value) {
                return true;
            }
        }

        return false;
    }

    public function isMap(): bool
    {
        return $this->location instanceof MapInterface;
    }

    public function getId(): int
    {
        return $this->location->getId();
    }

    public function getLayer(): ?LayerInterface
    {
        if ($this->location instanceof MapInterface) {
            return $this->location->getLayer();
        }

        $parentMap = $this->location->getSystem()->getMapField();
        if ($parentMap === null) {
            return null;
        }

        return $parentMap->getLayer();
    }

    public function getCx(): ?int
    {
        if ($this->parentMap === null) {
            return null;
        }

        return $this->parentMap->getCx();
    }

    public function getCy(): ?int
    {
        if ($this->parentMap === null) {
            return null;
        }

        return $this->parentMap->getCy();
    }
}
