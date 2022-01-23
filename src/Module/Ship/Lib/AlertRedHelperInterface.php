<?php

namespace Stu\Module\Ship\Lib;

use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ShipInterface;

interface AlertRedHelperInterface
{
    public function doItAll(ShipInterface $ship, ?GameControllerInterface $game): array;

    public function checkForAlertRedShips(ShipInterface $leadShip, &$informations): array;

    public function performAttackCycle(ShipInterface $alertShip, ShipInterface $leadShip, &$informations, $isColonyDefense = false): void;
}
