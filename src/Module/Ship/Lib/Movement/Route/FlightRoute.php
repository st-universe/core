<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use RuntimeException;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
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

    /** @var array<string, FlightConsequenceInterface>  */
    private array $flightConsequences;

    /** @var array<string, FlightConsequenceInterface> */
    private array $postFlightConsequences;

    //Members
    private bool $isTraversing = false;

    private int $routeMode = RouteModeEnum::ROUTE_MODE_FLIGHT;

    private MapInterface|StarSystemMapInterface $current;

    private ?WormholeEntryInterface $wormholeEntry = null;

    /**
     * @var Collection<int, MapInterface|StarSystemMapInterface>
     */
    private Collection $waypoints;

    /**
     * @param array<string, FlightConsequenceInterface> $flightConsequences
     * @param array<string, FlightConsequenceInterface> $postFlightConsequences
     */
    public function __construct(
        CheckDestinationInterface $checkDestination,
        LoadWaypointsInterface $loadWaypoints,
        EnterWaypointInterface $enterWaypoint,
        array $flightConsequences,
        array $postFlightConsequences,
    ) {
        $this->checkDestination = $checkDestination;
        $this->loadWaypoints = $loadWaypoints;
        $this->enterWaypoint = $enterWaypoint;
        $this->flightConsequences = $flightConsequences;
        $this->postFlightConsequences = $postFlightConsequences;

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
        $this->current = $start;
        $destination = $this->checkDestination->validate($ship, $x, $y);

        if ($start !== $destination) {
            $this->waypoints = $this->loadWaypoints->load($start, $destination);
            $this->isTraversing = true;
        }

        return $this;
    }

    public function getCurrentWaypoint(): MapInterface|StarSystemMapInterface
    {
        return $this->current;
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
        ShipWrapperInterface $wrapper,
        FightMessageCollectionInterface $messages
    ): void {

        // flight consequences
        $this->walkConsequences($this->flightConsequences, $wrapper, $messages);

        // enter waypoint
        $this->enterWaypoint->enterNextWaypoint(
            $wrapper->get(),
            $this->isTraversing,
            $this->getNextWaypoint(),
            $this->wormholeEntry
        );

        // post flight consequences
        $this->walkConsequences($this->postFlightConsequences, $wrapper, $messages);
    }

    /**
     * @param array<string, FlightConsequenceInterface> $consequences
     */
    private function walkConsequences(
        array $consequences,
        ShipWrapperInterface $wrapper,
        FightMessageCollectionInterface $messages
    ): void {
        array_walk(
            $consequences,
            fn (FlightConsequenceInterface $consequence) => $consequence->trigger($wrapper, $this, $messages)
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

    public function isTraversing(): bool
    {
        return $this->isTraversing;
    }

    public function isImpulseDriveNeeded(): bool
    {
        $routeMode = $this->getRouteMode();

        if (
            $routeMode === RouteModeEnum::ROUTE_MODE_SYSTEM_ENTRY
            || $routeMode === RouteModeEnum::ROUTE_MODE_WORMHOLE_ENTRY
        ) {
            return true;
        }

        return $routeMode === RouteModeEnum::ROUTE_MODE_FLIGHT
            && $this->getNextWaypoint() instanceof StarSystemMapInterface;
    }

    public function isWarpDriveNeeded(): bool
    {
        $routeMode = $this->getRouteMode();

        if (
            $routeMode === RouteModeEnum::ROUTE_MODE_SYSTEM_EXIT
            || $routeMode === RouteModeEnum::ROUTE_MODE_TRANSWARP
        ) {
            return true;
        }

        return $routeMode === RouteModeEnum::ROUTE_MODE_FLIGHT
            && $this->getNextWaypoint() instanceof MapInterface;
    }

    public function isTranswarpCoilNeeded(): bool
    {
        return $this->getRouteMode() === RouteModeEnum::ROUTE_MODE_TRANSWARP;
    }
}
