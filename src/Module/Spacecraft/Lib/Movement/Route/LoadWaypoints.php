<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use RuntimeException;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class LoadWaypoints implements LoadWaypointsInterface
{
    public function __construct(private MapRepositoryInterface $mapRepository, private StarSystemMapRepositoryInterface $starSystemMapRepository) {}

    #[\Override]
    public function load(
        Location $start,
        Location $destination
    ): Collection {
        if ($start instanceof Map !== $destination instanceof Map) {
            throw new InvalidArgumentException('start and destination have different type');
        }

        $startX = $start->getX();
        $startY = $start->getY();

        $destinationX = $destination->getX();
        $destinationY = $destination->getY();

        $sortAscending = true;

        if ($startY > $destinationY) {
            $sortAscending = false;
        }
        if ($startX > $destinationX) {
            $sortAscending = false;
        }
        if ($start instanceof StarSystemMap) {
            $waypoints = $this->starSystemMapRepository->getByCoordinateRange(
                $start->getSystem()->getId(),
                min($startX, $destinationX),
                max($startX, $destinationX),
                min($startY, $destinationY),
                max($startY, $destinationY),
                $sortAscending
            );
        } else {
            $layer = $start->getLayer();
            if ($layer === null) {
                throw new RuntimeException('this should not happen');
            }
            $waypoints = $this->mapRepository->getByCoordinateRange(
                $layer->getId(),
                min($startX, $destinationX),
                max($startX, $destinationX),
                min($startY, $destinationY),
                max($startY, $destinationY),
                $sortAscending
            );
        }

        $result = new ArrayCollection();

        foreach ($waypoints as $waypoint) {
            if ($waypoint !== $start) {
                $result->add($waypoint);
            }
        }

        return $result;
    }
}
