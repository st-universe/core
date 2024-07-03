<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component;

use Override;
use RuntimeException;
use Stu\Component\Ship\ShipEnum;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

final class UpdateFlightDirection implements UpdateFlightDirectionInterface
{
    #[Override]
    public function updateWhenTraversing(
        MapInterface|StarSystemMapInterface $oldWaypoint,
        MapInterface|StarSystemMapInterface $waypoint,
        ShipInterface $ship
    ): int {

        $startX = $oldWaypoint->getX();
        $startY = $oldWaypoint->getY();

        $destinationX = $waypoint->getX();
        $destinationY = $waypoint->getY();

        $flightDirection = null;

        if ($destinationX === $startX) {
            $oldy = $startY;
            if ($destinationY > $oldy) {
                $flightDirection = ShipEnum::DIRECTION_BOTTOM;
            } elseif ($destinationY < $oldy) {
                $flightDirection = ShipEnum::DIRECTION_TOP;
            }
        }
        if ($destinationY === $startY) {
            $oldx = $startX;
            if ($destinationX > $oldx) {
                $flightDirection = ShipEnum::DIRECTION_RIGHT;
            } elseif ($destinationX < $oldx) {
                $flightDirection = ShipEnum::DIRECTION_LEFT;
            }
        }

        if ($flightDirection === null) {
            throw new RuntimeException('this should not happen');
        }

        $ship->setFlightDirection($flightDirection);

        return $flightDirection;
    }

    #[Override]
    public function updateWhenSystemExit(ShipInterface $ship, StarSystemMapInterface $starsystemMap): void
    {
        $system = $starsystemMap->getSystem();

        $shipX = $starsystemMap->getSx();
        $shipY = $starsystemMap->getSy();

        $rad12or34 = atan($shipY / $shipX);
        $rad14or23 = atan(($system->getMaxX() - $shipX) / $shipY);

        if ($rad12or34 < M_PI_4) {
            $flightDirection = $rad14or23 < M_PI_4 ? ShipEnum::DIRECTION_LEFT : ShipEnum::DIRECTION_BOTTOM;
        } elseif ($rad14or23 < M_PI_4) {
            $flightDirection = ShipEnum::DIRECTION_TOP;
        } else {
            $flightDirection = ShipEnum::DIRECTION_RIGHT;
        }

        $ship->setFlightDirection($flightDirection);
    }
}
