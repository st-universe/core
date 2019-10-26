<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

interface PositionCheckerInterface
{
    public function checkPosition(ShipInterface $shipa, ShipInterface $shipb): bool;

    public function checkColonyPosition(ColonyInterface $col, ShipInterface $ship): bool;
}
