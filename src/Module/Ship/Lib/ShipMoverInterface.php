<?php

namespace Stu\Module\Ship\Lib;

interface ShipMoverInterface
{

    /**
     * @return array<string>
     */
    public function checkAndMove(
        ShipWrapperInterface $leadShip,
        int $destinationX,
        int $destinationY
    ): array;
}
