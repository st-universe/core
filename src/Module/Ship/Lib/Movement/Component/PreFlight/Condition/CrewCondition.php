<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\PreFlight\Condition;

use Stu\Module\Ship\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class CrewCondition implements PreFlightConditionInterface
{
    public function check(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        ConditionCheckResult $conditionCheckResult
    ): void {
        $ship = $wrapper->get();

        if (!$ship->hasEnoughCrew()) {
            $conditionCheckResult->addBlockedShip(
                $ship,
                sprintf(
                    'Die %s hat ungenügend Crew',
                    $ship->getName()
                )
            );
        }
    }
}
