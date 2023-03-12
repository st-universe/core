<?php

namespace Stu\Module\Ship\Lib;

interface ShipMoverInterface
{
    public function getInformations(): array;

    public function checkAndMove(
        ShipWrapperInterface $leadShip,
        int $destinationX,
        int $destinationY
    );
}
