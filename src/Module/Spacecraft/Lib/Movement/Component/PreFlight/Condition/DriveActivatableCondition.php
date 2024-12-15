<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\Condition;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class DriveActivatableCondition implements PreFlightConditionInterface
{
    public function __construct(private ActivatorDeactivatorHelperInterface $activatorDeactivatorHelper) {}

    #[Override]
    public function check(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        ConditionCheckResult $conditionCheckResult
    ): void {

        if ($flightRoute->isImpulseDriveNeeded()) {
            $this->activatorDeactivatorHelper->activate(
                $wrapper,
                SpacecraftSystemTypeEnum::SYSTEM_IMPULSEDRIVE,
                $conditionCheckResult,
                false,
                true
            );
        }

        if ($flightRoute->isWarpDriveNeeded()) {
            $this->activatorDeactivatorHelper->activate(
                $wrapper,
                SpacecraftSystemTypeEnum::SYSTEM_WARPDRIVE,
                $conditionCheckResult,
                false,
                true
            );
        }

        if ($flightRoute->isTranswarpCoilNeeded()) {
            $this->activatorDeactivatorHelper->activate(
                $wrapper,
                SpacecraftSystemTypeEnum::SYSTEM_TRANSWARP_COIL,
                $conditionCheckResult,
                false,
                true
            );
        }
    }
}
