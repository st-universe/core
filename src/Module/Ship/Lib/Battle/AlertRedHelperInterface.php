<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ShipInterface;

interface AlertRedHelperInterface
{
    public function doItAll(
        ShipInterface $ship,
        ?GameControllerInterface $game = null,
        ?ShipInterface $tractoringShip = null
    ): ?InformationWrapper;

    /**
     * @return array<ShipInterface>
     */
    public function checkForAlertRedShips(
        ShipInterface $leadShip,
        InformationWrapper $informations,
        ?ShipInterface $tractoringShip = null
    ): array;

    public function performAttackCycle(
        ShipInterface $alertShip,
        ShipInterface $leadShip,
        InformationWrapper $informations,
        bool $isColonyDefense = false
    ): void;
}
