<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ShipInterface;

interface ShipMoverInterface
{
    public function getInformations(): array;

    public function checkAndMove(ShipInterface $leadShip, int $destinationX, int $destinationY);
}
