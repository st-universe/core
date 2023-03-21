<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface AlertRedHelperInterface
{
    /**
     * @return array<string>
     */
    public function doItAll(
        ShipInterface $ship,
        ?GameControllerInterface $game,
        ?ShipInterface $tractoringShip = null
    ): array;

    /**
     * @return ShipWrapperInterface[]
     */
    public function getShips(ShipInterface $leadShip): array;

    /**
     * @param array<string> $informations
     * 
     * @return array<ShipInterface>
     */
    public function checkForAlertRedShips(
        ShipInterface $leadShip,
        &$informations,
        ?ShipInterface $tractoringShip = null
    ): array;

    /**
     * @param array<string> $informations
     */
    public function performAttackCycle(
        ShipInterface $alertShip,
        ShipInterface $leadShip,
        &$informations,
        bool $isColonyDefense = false
    ): void;
}
