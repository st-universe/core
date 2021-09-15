<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

final class PositionChecker implements PositionCheckerInterface
{

    public function checkPosition(ShipInterface $shipa, ShipInterface $shipb): bool
    {
        return $shipa->getMap() === $shipb->getMap() && $shipa->getStarsystemMap() === $shipb->getStarsystemMap();
    }

    public function checkColonyPosition(ColonyInterface $col, ShipInterface $ship): bool
    {
        if ($ship->getSystem() === null) {
            return false;
        }
        if ($col->getSystemsId() != $ship->getSystem()->getId()) {
            return false;
        }
        if ($col->getSX() != $ship->getSX() || $col->getSY() != $ship->getSY()) {
            return false;
        }
        return true;
    }
}
