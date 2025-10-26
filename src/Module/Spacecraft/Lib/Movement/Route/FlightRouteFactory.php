<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Stu\Component\Map\Effects\EffectHandlingInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\WormholeEntry;

final class FlightRouteFactory implements FlightRouteFactoryInterface
{
    /**
     * @param array<string, FlightConsequenceInterface> $flightConsequences
     * @param array<string, FlightConsequenceInterface> $postFlightConsequences
     */
    public function __construct(
        private CheckDestinationInterface $checkDestination,
        private LoadWaypointsInterface $loadWaypoints,
        private EnterWaypoint $enterWaypoint,
        private EffectHandlingInterface $effectHandling,
        private array $flightConsequences,
        private array $postFlightConsequences
    ) {}

    #[\Override]
    public function getRouteForMapDestination(
        Map|StarSystemMap $destination,
        bool $isTranswarp = false
    ): FlightRouteInterface {
        return $this->getFlightRoutePrototype()->setDestination($destination, $isTranswarp);
    }

    #[\Override]
    public function getRouteForWormholeDestination(WormholeEntry $destination, bool $isEntry): FlightRouteInterface
    {
        return $this->getFlightRoutePrototype()->setDestinationViaWormhole($destination, $isEntry);
    }

    #[\Override]
    public function getRouteForCoordinateDestination(Spacecraft $spacecraft, int $x, int $y): FlightRouteInterface
    {
        return $this->getFlightRoutePrototype()->setDestinationViaCoordinates($spacecraft, $x, $y);
    }

    private function getFlightRoutePrototype(): FlightRoute
    {
        return new FlightRoute(
            $this->checkDestination,
            $this->loadWaypoints,
            $this->enterWaypoint,
            $this->effectHandling,
            $this->flightConsequences,
            $this->postFlightConsequences
        );
    }
}
