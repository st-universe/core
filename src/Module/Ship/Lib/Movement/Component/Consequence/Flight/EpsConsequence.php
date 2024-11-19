<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Override;
use RuntimeException;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;

class EpsConsequence extends AbstractFlightConsequence implements FlightStartConsequenceInterface
{
    #[Override]
    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {
        if ($wrapper->get()->isTractored()) {
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null) {
            throw new RuntimeException('this should not happen');
        }

        $neededEps = $this->getEpsNeededForFlight($flightRoute, $wrapper->get());

        if ($neededEps > 0) {
            $epsSystem->lowerEps($neededEps)->update();
        }
    }

    private function getEpsNeededForFlight(FlightRouteInterface $flightRoute, ShipInterface $ship): int
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
