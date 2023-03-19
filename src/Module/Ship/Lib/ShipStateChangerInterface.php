<?php

namespace Stu\Module\Ship\Lib;

interface ShipStateChangerInterface
{
    public function changeShipState(ShipWrapperInterface $wrapper, int $newState): void;
}
