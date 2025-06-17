<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component;

use Override;
use RuntimeException;
use Stu\Component\Map\DirectionEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

final class UpdateFlightDirection implements UpdateFlightDirectionInterface
{
    #[Override]
    public function updateWhenTraversing(
        LocationInterface $oldWaypoint,
        LocationInterface $waypoint,
        SpacecraftWrapperInterface $wrapper
    ): DirectionEnum {

        $startX = $oldWaypoint->getX();
        $startY = $oldWaypoint->getY();

        $destinationX = $waypoint->getX();
        $destinationY = $waypoint->getY();

        $flightDirection = null;

        if ($destinationX === $startX) {
            $oldy = $startY;
            if ($destinationY > $oldy) {
                $flightDirection = DirectionEnum::BOTTOM;
            } elseif ($destinationY < $oldy) {
                $flightDirection = DirectionEnum::TOP;
            }
        }
        if ($destinationY === $startY) {
            $oldx = $startX;
            if ($destinationX > $oldx) {
                $flightDirection = DirectionEnum::RIGHT;
            } elseif ($destinationX < $oldx) {
                $flightDirection = DirectionEnum::LEFT;
            }
        }

        if ($flightDirection === null) {
            throw new RuntimeException('this should not happen');
        }

        $wrapper->getComputerSystemDataMandatory()->setFlightDirection($flightDirection)->update();

        return $flightDirection;
    }

    #[Override]
    public function updateWhenSystemExit(SpacecraftWrapperInterface $wrapper, StarSystemMapInterface $starsystemMap): void
    {
        $system = $starsystemMap->getSystem();

        $shipX = $starsystemMap->getSx();
        $shipY = $starsystemMap->getSy();

        $rad12or34 = atan($shipY / $shipX);
        $rad14or23 = atan(($system->getMaxX() - $shipX) / $shipY);

        if ($rad12or34 < M_PI_4) {
            $flightDirection = $rad14or23 < M_PI_4 ? DirectionEnum::LEFT : DirectionEnum::BOTTOM;
        } elseif ($rad14or23 < M_PI_4) {
            $flightDirection = DirectionEnum::TOP;
        } else {
            $flightDirection = DirectionEnum::RIGHT;
        }

        $wrapper->getComputerSystemDataMandatory()->setFlightDirection($flightDirection)->update();
    }
}
