<?php

declare(strict_types=1);

namespace Stu\Lib\Map;

use Override;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;

class DistanceCalculation implements DistanceCalculationInterface
{
    #[Override]
    public function shipToShipDistance(ShipInterface $ship, ShipInterface $station): int
    {

        return $this->calculateAbsoluteDistance(
            $ship->getSystem(),
            $station->getSystem(),
            $ship->getPosX(),
            $ship->getPosY(),
            $station->getPosX(),
            $station->getPosY()
        );
    }

    #[Override]
    public function shipToColonyDistance(ShipInterface $ship, ColonyInterface $colony): int
    {
        return $this->calculateAbsoluteDistance(
            $ship->getSystem(),
            $colony->getSystem(),
            $ship->getPosX(),
            $ship->getPosY(),
            $colony->getSx(),
            $colony->getSy()
        );
    }

    private function calculateAbsoluteDistance(
        ?StarSystemInterface $system1,
        ?StarSystemInterface $system2,
        int $x1,
        int $y1,
        int $x2,
        int $y2
    ): int {
        if ($this->isInSameSystem($system1, $system2)) {
            return $this->calculateDistance($x1, $x2, $y1, $y2, 1);
        }

        $jumpIntoSystemMarker = 0;
        if ($system1 !== null) {
            $jumpIntoSystemMarker += 1;
        }
        if ($system2 !== null) {
            $jumpIntoSystemMarker += 1;
        }

        $systemBorderDistance1 = $this->calculateSystemBorderDistance($system1, $x1, $y1);
        $systemBorderDistance2 = $this->calculateSystemBorderDistance($system2, $x2, $y2);

        $outerSystemDistance = $this->calculateDistance(
            $system1 === null ? $x1 : $system1->getCx(),
            $system2 === null ? $x2 : $system2->getCx(),
            $system1 === null ? $y1 : $system1->getCy(),
            $system2 === null ? $y2 : $system2->getCy(),
            1000
        );

        return $jumpIntoSystemMarker
            + $systemBorderDistance1
            + $systemBorderDistance2
            + $outerSystemDistance;
    }

    private function isInSameSystem(
        ?StarSystemInterface $system1,
        ?StarSystemInterface $system2
    ): bool {
        if ($system1 === null || $system2 === null) {
            return false;
        }

        return $system1 === $system2;
    }

    private function calculateSystemBorderDistance(
        ?StarSystemInterface $system,
        int $x,
        int $y,
    ): int {
        if ($system === null) {
            return 0;
        }

        $systemWidth = $system->getMaxX();
        $systemHeight = $system->getMaxY();

        $borderDistanceX = min($x - 1, $systemWidth - $x);
        $borderDistanceY = min($y - 1, $systemHeight - $y);

        return $borderDistanceX + $borderDistanceY;
    }

    private function calculateDistance(
        int $x1,
        int $x2,
        int $y1,
        int $y2,
        int $multiplier
    ): int {
        return (abs($x1 - $x2) + abs($y1 - $y2)) * $multiplier;
    }
}
