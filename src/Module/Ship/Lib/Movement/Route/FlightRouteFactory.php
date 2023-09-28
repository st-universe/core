<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Stu\Module\Ship\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\WormholeEntryInterface;

final class FlightRouteFactory implements FlightRouteFactoryInterface
{
    private CheckDestinationInterface $checkDestination;

    private LoadWaypointsInterface $loadWaypoints;

    private EnterWaypoint $enterWaypoint;

    /** @var array<string, FlightConsequenceInterface>  */
    private array $flightConsequences;

    /** @var array<string, FlightConsequenceInterface> */
    private array $postFlightConsequences;

    /**
     * @param array<string, FlightConsequenceInterface> $flightConsequences
     * @param array<string, FlightConsequenceInterface> $postFlightConsequences
     */
    public function __construct(
        CheckDestinationInterface $checkDestination,
        LoadWaypointsInterface $loadWaypoints,
        EnterWaypoint $enterWaypoint,
        array $flightConsequences,
        array $postFlightConsequences,
    ) {
        $this->checkDestination = $checkDestination;
        $this->loadWaypoints = $loadWaypoints;
        $this->enterWaypoint = $enterWaypoint;
        $this->flightConsequences = $flightConsequences;
        $this->postFlightConsequences = $postFlightConsequences;
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
            $this->enterWaypoint,
            $this->flightConsequences,
            $this->postFlightConsequences
        );
    }
}
