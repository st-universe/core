<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Stu\Lib\InformationWrapper;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\Movement\Component\CheckAstronomicalWaypointInterface;
use Stu\Module\Ship\Lib\Movement\Component\FlightSignatureCreatorInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\WormholeEntryInterface;
use Stu\Orm\Repository\WormholeEntryRepositoryInterface;

final class EnterWaypoint implements EnterWaypointInterface
{
    private FlightSignatureCreatorInterface $flightSignatureCreator;

    private UpdateFlightDirectionInterface $updateFlightDirection;

    private WormholeEntryRepositoryInterface $wormholeEntryRepository;

    private CheckAstronomicalWaypointInterface $checkAstronomicalWaypoint;

    private StuTime $stuTime;

    public function __construct(
        FlightSignatureCreatorInterface $flightSignatureCreator,
        UpdateFlightDirectionInterface $updateFlightDirection,
        WormholeEntryRepositoryInterface $wormholeEntryRepository,
        CheckAstronomicalWaypointInterface $checkAstronomicalWaypoint,
        StuTime $stuTime
    ) {
        $this->flightSignatureCreator = $flightSignatureCreator;
        $this->updateFlightDirection = $updateFlightDirection;
        $this->wormholeEntryRepository = $wormholeEntryRepository;
        $this->checkAstronomicalWaypoint = $checkAstronomicalWaypoint;
        $this->stuTime = $stuTime;
    }

    public function enterNextWaypoint(
        ShipInterface $ship,
        bool $isTraversing,
        MapInterface|StarSystemMapInterface $waypoint,
        ?WormholeEntryInterface $wormholeEntry,
        InformationWrapper $informations
    ): void {
        $oldWaypoint = $ship->getCurrentMapField();

        $ship->updateLocation(
            $waypoint instanceof MapInterface ? $waypoint : null,
            $waypoint instanceof StarSystemMapInterface ? $waypoint : null
        );

        if ($this->isWormhole($waypoint)) {
            $ship->setCx(0);
            $ship->setCy(0);
        }

        if ($wormholeEntry !== null) {
            $wormholeEntry->setLastUsed($this->stuTime->time());
            $this->wormholeEntryRepository->save($wormholeEntry);
        }

        if ($isTraversing) {
            $flightDirection = $this->updateFlightDirection->updateWhenTraversing(
                $oldWaypoint,
                $waypoint,
                $ship
            );

            //create flight signatures
            $this->flightSignatureCreator->createSignatures(
                $ship,
                $flightDirection,
                $oldWaypoint,
                $waypoint,
            );
        }

        //leaving star system
        if (
            $oldWaypoint instanceof StarSystemMapInterface
            && $waypoint instanceof MapInterface
            && !$oldWaypoint->getSystem()->isWormhole()
        ) {
            $this->updateFlightDirection->updateWhenSystemExit($ship, $oldWaypoint);
        }

        if ($ship->getSystem() !== null || $ship->getMapRegion() !== null) {
            $this->checkAstronomicalWaypoint->checkWaypoint($ship, $informations);
        }
    }

    private function isWormhole(MapInterface|StarSystemMapInterface $waypoint): bool
    {
        if ($waypoint instanceof MapInterface) {
            return false;
        }

        return $waypoint->getSystem()->isWormhole();
    }
}
