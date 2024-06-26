<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\WormholeEntryInterface;
use Stu\Orm\Repository\WormholeEntryRepositoryInterface;

final class EnterWaypoint implements EnterWaypointInterface
{
    public function __construct(
        private WormholeEntryRepositoryInterface $wormholeEntryRepository,
        private StuTime $stuTime
    ) {
    }

    public function enterNextWaypoint(
        ShipInterface $ship,
        bool $isTraversing,
        MapInterface|StarSystemMapInterface $waypoint,
        ?WormholeEntryInterface $wormholeEntry
    ): void {

        $ship->updateLocation($waypoint);

        if ($wormholeEntry !== null) {
            $wormholeEntry->setLastUsed($this->stuTime->time());
            $this->wormholeEntryRepository->save($wormholeEntry);
        }
    }
}
