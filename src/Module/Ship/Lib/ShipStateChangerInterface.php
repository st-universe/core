<?php

namespace Stu\Module\Ship\Lib;

interface ShipStateChangerInterface
{
    public function changeShipState(
        ShipWrapperInterface $wrapper,
        int $newState
    ): void;

    public function changeAlertState(
        ShipWrapperInterface $wrapper,
        int $alertState
    ): ?string;
}
