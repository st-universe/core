<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface ShipAttackCycleInterface
{
    /**
     * @param ShipWrapperInterface[] $attackingShips indexed by ship id
     * @param ShipWrapperInterface[] $defendingShips indexed by ship id
     * @param bool $oneWay only attackers fire
     * @param bool $isAlertRed attack started due to alert red situation
     */
    public function cycle(
        array $attackingShips,
        array $defendingShips,
        bool $oneWay = false,
        bool $isAlertRed = false
    ): MessageCollectionInterface;
}
