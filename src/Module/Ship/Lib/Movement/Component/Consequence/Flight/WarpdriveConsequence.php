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
use Stu\Orm\Entity\StarSystemMapInterface;

class WarpdriveConsequence extends AbstractFlightConsequence implements FlightStartConsequenceInterface
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

        $routeMode = $flightRoute->getRouteMode();
        if ($routeMode !== RouteModeEnum::ROUTE_MODE_FLIGHT) {
            return;
        }

        if ($flightRoute->getNextWaypoint() instanceof StarSystemMapInterface) {
            return;
        }

        $warpdriveSystem = $wrapper->getWarpDriveSystemData();
        if ($warpdriveSystem === null) {
            throw new RuntimeException('this should not happen');
        }

        $ship = $wrapper->get();

        $neededWarpDrive = 1;
        if ($ship->isTractoring()) {
            $neededWarpDrive += 2;
        }

        $warpdriveSystem->lowerWarpDrive($neededWarpDrive)->update();
    }
}
