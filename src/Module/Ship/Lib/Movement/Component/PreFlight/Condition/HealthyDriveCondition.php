<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\PreFlight\Condition;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class HealthyDriveCondition implements PreFlightConditionInterface
{
    public function check(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        ConditionCheckResult $conditionCheckResult
    ): void {

        if ($flightRoute->isImpulseDriveNeeded()) {
            $this->checkSystemHealth(
                $wrapper,
                ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE,
                $conditionCheckResult
            );
        }

        if ($flightRoute->isWarpDriveNeeded()) {
            $this->checkSystemHealth(
                $wrapper,
                ShipSystemTypeEnum::SYSTEM_WARPDRIVE,
                $conditionCheckResult
            );
        }

        if ($flightRoute->isTranswarpCoilNeeded()) {
            $this->checkSystemHealth(
                $wrapper,
                ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL,
                $conditionCheckResult
            );
        }
    }

    private function checkSystemHealth(
        ShipWrapperInterface $wrapper,
        int $systemId,
        ConditionCheckResult $conditionCheckResult
    ): void {
        $ship = $wrapper->get();

        if (!$ship->hasShipSystem($systemId)) {
            $conditionCheckResult->addBlockedShip(
                $ship,
                sprintf(
                    'Die %s verfügt über keine(n) %s',
                    $ship->getName(),
                    ShipSystemTypeEnum::getDescription($systemId)
                )
            );

            return;
        }

        if (!$ship->isSystemHealthy($systemId)) {
            $conditionCheckResult->addBlockedShip(
                $ship,
                sprintf(
                    'Die %s kann das System %s nicht aktivieren',
                    $ship->getName(),
                    ShipSystemTypeEnum::getDescription($systemId)
                )
            );
        }
    }
}
