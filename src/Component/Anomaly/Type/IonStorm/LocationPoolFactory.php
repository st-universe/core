<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type\IonStorm;

use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

class LocationPoolFactory
{
    public function __construct(
        private MapRepositoryInterface $mapRepository,
        private StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {}

    public function createLocationPool(AnomalyInterface $anomaly, int $stretch): LocationPool
    {
        return new LocationPool($this->getLocations($anomaly, $stretch));
    }

    /** @return array<string, LocationInterface> */
    private function getLocations(AnomalyInterface $anomaly, int $stretch): array
    {
        $panelBoundaries = null;
        foreach ($anomaly->getChildren() as $child) {

            $location = $child->getLocation();
            if ($location === null) {
                continue;
            }

            if ($panelBoundaries === null) {
                $panelBoundaries = PanelBoundaries::fromLocation($location, 0);
            } else {
                $panelBoundaries->extendBy($location);
            }
        }

        if ($panelBoundaries === null) {
            return [];
        }

        $panelBoundaries->stretch($stretch);

        $locationArray = $panelBoundaries->isOnMap()
            ? $this->mapRepository->getByBoundaries($panelBoundaries)
            : $this->starSystemMapRepository->getByBoundaries($panelBoundaries);

        $indexedArray = [];
        foreach ($locationArray as $location) {
            $indexedArray[sprintf('%d_%d', $location->getX(), $location->getY())] = $location;
        }

        return $indexedArray;
    }
}
