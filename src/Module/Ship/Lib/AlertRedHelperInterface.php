<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ShipInterface;

interface AlertRedHelperInterface
{
    public function checkForAlertRedShips(ShipInterface $leadShip, &$informations): array;

    public function performAttackCycle(ShipInterface $alertShip, ShipInterface $leadShip, &$informations, $isColonyDefense = false): void;
}
