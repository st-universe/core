<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\PreFlight\Condition;

use Override;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class DriveActivatableCondition implements PreFlightConditionInterface
{
    public function __construct(private ActivatorDeactivatorHelperInterface $activatorDeactivatorHelper)
    {
    }

    #[Override]
    public function check(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        ConditionCheckResult $conditionCheckResult
    ): void {

        if ($flightRoute->isImpulseDriveNeeded()) {
            $this->activatorDeactivatorHelper->activate(
                $wrapper,
                ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE,
                $conditionCheckResult,
                false,
                true
            );
        }

        if ($flightRoute->isWarpDriveNeeded()) {
            $this->activatorDeactivatorHelper->activate(
                $wrapper,
                ShipSystemTypeEnum::SYSTEM_WARPDRIVE,
                $conditionCheckResult,
                false,
                true
            );
        }

        if ($flightRoute->isTranswarpCoilNeeded()) {
            $this->activatorDeactivatorHelper->activate(
                $wrapper,
                ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL,
                $conditionCheckResult,
                false,
                true
            );
        }
    }
}
