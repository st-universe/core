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

    public function __construct(?MapInterface $map, ?StarSystemMapInterface $sysMap)
    {
        if (
            $map === null && $sysMap === null
            || $map !== null && $sysMap !== null
        ) {
            throw new InvalidArgumentException('Either map or systemMap has to be filled');
        }

        $this->location = $map ?? $sysMap;
    }

    public function get(): MapInterface|StarSystemMapInterface
    {
        return $this->location;
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
}
