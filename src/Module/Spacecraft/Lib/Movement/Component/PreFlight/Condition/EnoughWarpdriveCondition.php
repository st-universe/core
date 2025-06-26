<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\Condition;

use Override;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RouteModeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\StarSystemMap;

class EnoughWarpdriveCondition implements PreFlightConditionInterface
{
    #[Override]
    public function check(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        ConditionCheckResult $conditionCheckResult
    ): void {

        $routeMode = $flightRoute->getRouteMode();
        if ($routeMode !== RouteModeEnum::FLIGHT) {
            return;
        }

        if ($flightRoute->getNextWaypoint() instanceof StarSystemMap) {
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
