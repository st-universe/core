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
    //Components
    private CheckDestinationInterface $checkDestination;

    private LoadWaypointsInterface $loadWaypoints;

    private EnterWaypointInterface $enterWaypoint;

    //Members
    private bool $isTraversing = false;

    private int $routeMode = RouteModeEnum::ROUTE_MODE_FLIGHT;

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

        if ($destination instanceof MapInterface) {
            $this->routeMode = RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT;
        } else {
            $this->routeMode = RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY;
        }

        return $this;
    }

    public function setDestinationViaWormhole(WormholeEntryInterface $wormholeEntry, bool $isEntry): FlightRouteInterface
    {
        $this->wormholeEntry = $wormholeEntry;
        if ($isEntry) {
            $this->waypoints->add($wormholeEntry->getSystemMap());
            $this->routeMode = RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY;
        } else {
            $this->waypoints->add($wormholeEntry->getMap());
            $this->routeMode = RouteModeEnum::ROUTE_MODE_WORMHOLE_EXIT;
        }

        return $this;
    }

    public function setDestinationViaCoordinates(ShipInterface $ship, int $x, int $y): FlightRouteInterface
    {
        $start = $ship->getCurrentMapField();
        $destination = $this->checkDestination->validate($ship, $x, $y);

        if ($start !== $destination) {
            $this->waypoints = $this->loadWaypoints->load($start, $destination);
        }

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
            return;
        }

        $this->current = $first;
        $this->waypoints->removeElement($this->current);
    }

    public function abortFlight(): void
    {
        $this->waypoints->clear();
    }

    public function enterNextWaypoint(
        ShipInterface $ship,
        MapInterface|StarSystemMapInterface $nextWaypoint,
        InformationWrapper $informations
    ): void {
        $this->enterWaypoint->enterNextWaypoint(
            $ship,
            $this->isTraversing,
            $nextWaypoint,
            $this->wormholeEntry,
            $informations
        );
    }

    public function isDestinationArrived(): bool
    {
        return $this->waypoints->isEmpty();
    }

    public function getRouteMode(): int
    {
        return $this->routeMode;
    }
}
