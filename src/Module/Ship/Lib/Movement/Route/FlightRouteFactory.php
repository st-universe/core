<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Override;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\WormholeEntryInterface;

final class FlightRouteFactory implements FlightRouteFactoryInterface
{
    /**
     * @param array<string, FlightConsequenceInterface> $flightConsequences
     * @param array<string, FlightConsequenceInterface> $postFlightConsequences
     */
    public function __construct(private CheckDestinationInterface $checkDestination, private LoadWaypointsInterface $loadWaypoints, private EnterWaypoint $enterWaypoint, private array $flightConsequences, private array $postFlightConsequences)
    {
    }

    #[Override]
    public function getRouteForMapDestination(
        MapInterface|StarSystemMapInterface $destination,
        bool $isTranswarp = false
    ): FlightRouteInterface {
        return $this->getFlightRoutePrototype()->setDestination($destination, $isTranswarp);
    }

    #[Override]
    public function getRouteForWormholeDestination(WormholeEntryInterface $destination, bool $isEntry): FlightRouteInterface
    {
        return $this->getFlightRoutePrototype()->setDestinationViaWormhole($destination, $isEntry);
    }

    #[Override]
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
