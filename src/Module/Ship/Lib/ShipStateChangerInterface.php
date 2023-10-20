<?php

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipStateEnum;

interface ShipStateChangerInterface
{
    // alert energy consumption
    public const ALERT_YELLOW_EPS_USAGE = 1;
    public const ALERT_RED_EPS_USAGE = 2;

    public function changeShipState(
        ShipWrapperInterface $wrapper,
        ShipStateEnum $newState
    ): void;

    public function changeAlertState(
        ShipWrapperInterface $wrapper,
        ShipAlertStateEnum $alertState
    ): ?string;
}
