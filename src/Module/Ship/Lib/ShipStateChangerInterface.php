<?php

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\ShipStateEnum;

interface ShipStateChangerInterface
{
    public function changeShipState(
        ShipWrapperInterface $wrapper,
        ShipStateEnum $newState
    ): void;

    public function changeAlertState(
        ShipWrapperInterface $wrapper,
        int $alertState
    ): ?string;
}
