<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ShipWrapperInterface;

interface ShipAttackCycleInterface
{
    /**
     * @param ShipWrapperInterface[] $attackingShips indexed by ship id
     * @param ShipWrapperInterface[] $defendingShips indexed by ship id
     * @param bool $oneWay only attackers fire
     */
    public function init(
        array $attackingShips,
        array $defendingShips,
        bool $oneWay = false
    ): void;

    public function cycle(bool $isAlertRed = false);

    public function getMessages();
}
