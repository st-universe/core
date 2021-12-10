<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ShipInterface;

interface ShipAttackCycleInterface
{
    /**
     * @param ShipInterface[] $attackingShips indexed by ship id
     * @param ShipInterface $defendingShips indexed by ship id
     * @param bool $oneWay only attackers fire
     */
    public function init(
        array $attackingShips,
        array $defendingShips,
        bool $oneWay = false
    ): void;

    public function cycle(bool $isAlertRed = false);

    public function releaseSemaphores(): void;

    public function getMessages();
}
