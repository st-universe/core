<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Override;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\WormholeEntryInterface;
use Stu\Orm\Repository\WormholeEntryRepositoryInterface;

final class EnterWaypoint implements EnterWaypointInterface
{
    public function __construct(
        private WormholeEntryRepositoryInterface $wormholeEntryRepository,
        private StuTime $stuTime
    ) {}

    #[Override]
    public function enterNextWaypoint(
        ShipInterface $ship,
        bool $isTraversing,
        LocationInterface $waypoint,
        ?WormholeEntryInterface $wormholeEntry
    ): void {

        $ship->setLocation($waypoint);

        if ($wormholeEntry !== null) {
            $wormholeEntry->setLastUsed($this->stuTime->time());
            $this->wormholeEntryRepository->save($wormholeEntry);
        }
    }
}
