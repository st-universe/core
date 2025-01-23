<?php

namespace Stu\Module\Spacecraft\Lib;

use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface SpacecraftStateChangerInterface
{
    // alert energy consumption
    public const ALERT_YELLOW_EPS_USAGE = 1;
    public const ALERT_RED_EPS_USAGE = 2;

    public function changeShipState(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftStateEnum $newState
    ): void;

    public function changeAlertState(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftAlertStateEnum $alertState
    ): ?string;
}
