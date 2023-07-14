<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use RuntimeException;
use Stu\Lib\InformationWrapper;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\WormholeEntryInterface;

final class FlightRoute implements FlightRouteInterface
{
    private CheckDestinationInterface $checkDestination;

    private LoadWaypointsInterface $loadWaypoints;

    private EnterWaypointInterface $enterWaypoint;

    private bool $isTraversing = false;

    private MapInterface|StarSystemMapInterface $current;

    private ?WormholeEntryInterface $wormholeEntry = null;

    /**
     * @var Collection<int, MapInterface|StarSystemMapInterface>
     */
    private Collection $waypoints;

    public function __construct(
        CheckDestinationInterface $checkDestination,
        LoadWaypointsInterface $loadWaypoints,
        EnterWaypointInterface $enterWaypoint
    ) {
        $this->checkDestination = $checkDestination;
        $this->loadWaypoints = $loadWaypoints;
        $this->enterWaypoint = $enterWaypoint;

        $this->waypoints = new ArrayCollection();
    }

    public function setDestination(MapInterface|StarSystemMapInterface $destination): FlightRouteInterface
    {
        $this->waypoints->add($destination);

        return $this;
    }

    public function setDestinationViaWormhole(WormholeEntryInterface $wormholeEntry, bool $isEntry): FlightRouteInterface
    {
        $this->wormholeEntry = $wormholeEntry;
        if ($isEntry) {
            $this->waypoints->add($wormholeEntry->getSystemMap());
        } else {
            $this->waypoints->add($wormholeEntry->getMap());
        }

        return $this;
    }

    public function setDestinationViaCoordinates(ShipInterface $ship, int $x, int $y): FlightRouteInterface
    {
        $start = $ship->getCurrentMapField();
        $destination = $this->checkDestination->validate($ship, $x, $y);

        $this->waypoints = $this->loadWaypoints->load($start, $destination);

        return $this;
    }

    public function getNextWaypoint(): MapInterface|StarSystemMapInterface
    {
        if ($this->waypoints->isEmpty()) {
            throw new RuntimeException('isDestinationArrived has to be called beforehand');
        }

        return $this->waypoints->first();
    }

    public function stepForward(): void
    {
        $first =  $this->waypoints->first();

        if (!$first) {
            throw new RuntimeException('isDestinationArrived has to be called beforehand');
        }

        $this->current = $first;
        $this->waypoints->removeElement($this->current);
    }

    public function abortFlight(): void
    {
        $this->waypoints->clear();
    }

    public function enterNextWaypoint(ShipInterface $ship, InformationWrapper $informations): void
    {
        $this->enterWaypoint->enterNextWaypoint(
            $ship,
            $this->isTraversing,
            $this->getNextWaypoint(),
            $this->wormholeEntry,
            $informations
        );
    }

    public function isDestinationArrived(): bool
    {
        return $this->waypoints->isEmpty();
    }
}
