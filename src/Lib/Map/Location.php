<?php

declare(strict_types=1);

namespace Stu\Lib\Map;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ReadableCollection;
use InvalidArgumentException;
use Stu\Orm\Entity\AnomalyInterface;
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

    public function hasAnomaly(int $anomalyType): bool
    {
        foreach ($this->getAnomalies() as $anomaly) {
            if ($anomaly->getAnomalyType()->getId() === $anomalyType) {
                return true;
            }
        }

        return false;
    }
}
