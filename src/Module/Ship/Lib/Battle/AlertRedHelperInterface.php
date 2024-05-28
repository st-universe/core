<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Orm\Entity\ShipInterface;

interface AlertRedHelperInterface
{
    public function doItAll(
        ShipInterface $ship,
        InformationInterface $informations,
        ?ShipInterface $tractoringShip = null
    ): void;

    public function performAttackCycle(
        ShipInterface $alertShip,
        ShipInterface $leadShip,
        InformationWrapper $informations,
        bool $isColonyDefense = false
    ): void;
}
