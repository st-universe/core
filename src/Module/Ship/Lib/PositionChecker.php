<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

final class PositionChecker implements PositionCheckerInterface
{

    public function checkPosition(ShipInterface $shipa, ShipInterface $shipb): bool
    {
        if ($shipa->getSystem() !== null) {
            if ($shipb->getSystem() === null || $shipa->getSystem()->getId() !== $shipb->getSystem()->getId()) {
                return false;
            }
            if ($shipa->getSX() != $shipb->getSX() || $shipa->getSY() != $shipb->getSY()) {
                return false;
            }
            return true;
        }
        if ($shipa->getCX() != $shipb->getCX() || $shipa->getCY() != $shipb->getCY()) {
            return false;
        }
        return true;
    }

    public function checkColonyPosition(ColonyInterface $col, ShipInterface $ship): bool
    {
        if ($col->getSystemsId() != $ship->getSystem()->getId()) {
            return false;
        }
        if ($col->getSX() != $ship->getSX() || $col->getSY() != $ship->getSY()) {
            return false;
        }
        return true;
    }
}
