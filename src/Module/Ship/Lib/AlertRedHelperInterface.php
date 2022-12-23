<?php

namespace Stu\Module\Ship\Lib;

use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ShipInterface;

interface AlertRedHelperInterface
{
    public function doItAll(ShipInterface $ship, ?GameControllerInterface $game, ?ShipInterface $tractoringShip = null): array;

    /**
     * @return ShipWrapperInterface[]
     */
    public function getShips(ShipInterface $leadShip): array;

    public function checkForAlertRedShips(ShipInterface $leadShip, &$informations, ?ShipInterface $tractoringShip = null): array;

    public function performAttackCycle(ShipInterface $alertShip, ShipInterface $leadShip, &$informations, $isColonyDefense = false): void;
}
