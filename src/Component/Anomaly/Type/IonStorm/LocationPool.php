<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type\IonStorm;

use Stu\Orm\Entity\Location;

class LocationPool
{
    /** @param array<string, Location> $locations */
    public function __construct(private array $locations) {}

    public function getLocation(int $x, int $y): ?Location
    {
        $key = sprintf('%d_%d', $x, $y);
        if (!array_key_exists($key, $this->locations)) {
            return null;
        }

        return $this->locations[$key];
    }

    /** @return array<Location> */
    public function getNeighbours(Location $location): array
    {
        $result = [];

        $topLocation = $this->getLocation($location->getX(), $location->getY() - 1);
        $bottomLocation = $this->getLocation($location->getX(), $location->getY() + 1);
        $leftLocation = $this->getLocation($location->getX() - 1, $location->getY());
        $rightLocation = $this->getLocation($location->getX() + 1, $location->getY());

        if ($topLocation !== null) {
            $result[] = $topLocation;
        }
        if ($bottomLocation !== null) {
            $result[] = $bottomLocation;
        }
        if ($leftLocation !== null) {
            $result[] = $leftLocation;
        }
        if ($rightLocation !== null) {
            $result[] = $rightLocation;
        }

        return $result;
    }
}
