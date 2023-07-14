<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\WormholeEntryInterface;

final class FlightRouteFactory implements FlightRouteFactoryInterface
{
    private CheckDestinationInterface $checkDestination;

    private LoadWaypointsInterface $loadWaypoints;

    private EnterWaypoint $enterWaypoint;

    public function __construct(
        CheckDestinationInterface $checkDestination,
        LoadWaypointsInterface $loadWaypoints,
        EnterWaypoint $enterWaypoint
    ) {
        $this->checkDestination = $checkDestination;
        $this->loadWaypoints = $loadWaypoints;
        $this->enterWaypoint = $enterWaypoint;
    }

    public function getRouteForMapDestination(MapInterface|StarSystemMapInterface $destination): FlightRouteInterface
    {
        return $this->getFlightRoutePrototype()->setDestination($destination);
    }

    public function getRouteForWormholeDestination(WormholeEntryInterface $destination, bool $isEntry): FlightRouteInterface
    {
        return $this->getFlightRoutePrototype()->setDestinationViaWormhole($destination, $isEntry);
    }

    public function getRouteForCoordinateDestination(ShipInterface $ship, int $x, int $y): FlightRouteInterface
    {
        return $this->getFlightRoutePrototype()->setDestinationViaCoordinates($ship, $x, $y);
    }

    private function getFlightRoutePrototype(): FlightRoute
    {
        return new FlightRoute(
            $this->checkDestination,
            $this->loadWaypoints,
            $this->enterWaypoint
        );
    }
}
