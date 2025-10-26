<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\WormholeEntry;
use Stu\Orm\Repository\WormholeEntryRepositoryInterface;

final class EnterWaypoint implements EnterWaypointInterface
{
    public function __construct(
        private WormholeEntryRepositoryInterface $wormholeEntryRepository,
        private StuTime $stuTime
    ) {}

    #[\Override]
    public function enterNextWaypoint(
        ?Spacecraft $spacecraft,
        bool $isTraversing,
        Location $waypoint,
        ?WormholeEntry $wormholeEntry
    ): void {

        if ($spacecraft === null) {
            return;
        }

        $spacecraft->setLocation($waypoint);
        $waypoint->getSpacecrafts()->set($spacecraft->getId(), $spacecraft);

        if ($wormholeEntry !== null) {
            $wormholeEntry->setLastUsed($this->stuTime->time());
            $this->wormholeEntryRepository->save($wormholeEntry);
        }

        $this->enterNextWaypoint($spacecraft->getTractoredShip(), $isTraversing, $waypoint, $wormholeEntry);
    }
}
