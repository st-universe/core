<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\PreFlight\Condition;

use Stu\Module\Ship\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

class EnoughWarpdriveCondition implements PreFlightConditionInterface
{
    public function check(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        ConditionCheckResult $conditionCheckResult
    ): void {

        $routeMode = $flightRoute->getRouteMode();
        if ($routeMode !== RouteModeEnum::ROUTE_MODE_FLIGHT) {
            return;
        }

        if ($flightRoute->getNextWaypoint() instanceof StarSystemMapInterface) {
            return;
        }

        $warpdriveSystem = $wrapper->getWarpDriveSystemData();
        if ($warpdriveSystem === null) {
            return;
        }

        $ship = $wrapper->get();

        $neededWarpDrive = 1;
        if ($ship->isTractoring()) {
            $neededWarpDrive += 2;
        }

        if ($warpdriveSystem->getWarpDrive() < $neededWarpDrive) {
            $conditionCheckResult->addBlockedShip(
                $ship,
                sprintf(
                    'Die %s hat nicht genug Warpantriebsenergie für den %s (%d benötigt)',
                    $ship->getName(),
                    $ship->isTractoring() ? 'Traktor-Flug' : 'Flug',
                    $neededWarpDrive
                )
            );
        }
    }
}
