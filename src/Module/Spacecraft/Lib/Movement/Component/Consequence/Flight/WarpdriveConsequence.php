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
use Stu\Orm\Entity\StarSystemMapInterface;

class WarpdriveConsequence extends AbstractFlightConsequence implements FlightStartConsequenceInterface
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
