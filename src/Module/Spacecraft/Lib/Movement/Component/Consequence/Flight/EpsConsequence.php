<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight;

use Override;
use RuntimeException;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\SpacecraftInterface;

class EpsConsequence extends AbstractFlightConsequence implements FlightStartConsequenceInterface
{

    #[Override]
    protected function skipWhenTractored(): bool
    {
        return true;
    }

    #[Override]
    protected function triggerSpecific(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null) {
            throw new RuntimeException('this should not happen');
        }

        $neededEps = $this->getEpsNeededForFlight($flightRoute, $wrapper->get());

        if ($neededEps > 0) {
            $epsSystem->lowerEps($neededEps)->update();
        }
    }

    private function getEpsNeededForFlight(FlightRouteInterface $flightRoute, SpacecraftInterface $ship): int
    {
        if ($flightRoute->getRouteMode() !== RouteModeEnum::ROUTE_MODE_FLIGHT) {
            return 0;
        }

        $nextWaypoint = $flightRoute->getNextWaypoint();
        if ($nextWaypoint instanceof MapInterface) {
            return 0;
        }

        $result = $ship->getRump()->getFlightEcost();

        $tractoredShip = $ship->getTractoredShip();
        if ($tractoredShip !== null) {
            $result += $tractoredShip->getRump()->getFlightEcost();
        }

        return $result;
    }
}
