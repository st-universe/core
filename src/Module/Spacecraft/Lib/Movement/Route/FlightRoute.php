<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Override;
use RuntimeException;
use Stu\Component\Map\Effects\EffectHandlingInterface;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\FlightConsequenceInterface;
use Stu\Module\Spacecraft\Lib\Movement\FlightCompany;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Entity\WormholeEntryInterface;

final class FlightRoute implements FlightRouteInterface
{
    //Members
    private bool $isTraversing = false;

    private RouteModeEnum $routeMode = RouteModeEnum::FLIGHT;

    private LocationInterface $current;

    private ?WormholeEntryInterface $wormholeEntry = null;

    /**
     * @var Collection<int, LocationInterface>
     */
    private Collection $waypoints;

    /**
     * @param array<string, FlightConsequenceInterface> $flightConsequences
     * @param array<string, FlightConsequenceInterface> $postFlightConsequences
     */
    public function __construct(
        private CheckDestinationInterface $checkDestination,
        private LoadWaypointsInterface $loadWaypoints,
        private EnterWaypointInterface $enterWaypoint,
        private EffectHandlingInterface $effectHandling,
        private array $flightConsequences,
        private array $postFlightConsequences,
    ) {
        $this->waypoints = new ArrayCollection();
    }

    #[Override]
    public function setDestination(
        MapInterface|StarSystemMapInterface $destination,
        bool $isTranswarp
    ): FlightRouteInterface {
        $this->waypoints->add($destination);

        if ($destination instanceof MapInterface) {
            if ($isTranswarp) {
                $this->routeMode = RouteModeEnum::TRANSWARP;
            } else {
                $this->routeMode = RouteModeEnum::SYSTEM_EXIT;
            }
        } else {
            $this->routeMode = RouteModeEnum::SYSTEM_ENTRY;
        }

        return $this;
    }

    #[Override]
    public function setDestinationViaWormhole(WormholeEntryInterface $wormholeEntry, bool $isEntry): FlightRouteInterface
    {
        $this->wormholeEntry = $wormholeEntry;
        if ($isEntry) {
            $this->waypoints->add($wormholeEntry->getSystemMap());
            $this->routeMode = RouteModeEnum::WORMHOLE_ENTRY;
        } else {
            $this->waypoints->add($wormholeEntry->getMap());
            $this->routeMode = RouteModeEnum::WORMHOLE_EXIT;
        }

        return $this;
    }

    #[Override]
    public function setDestinationViaCoordinates(SpacecraftInterface $spacecraft, int $x, int $y): FlightRouteInterface
    {
        $start = $spacecraft->getLocation();
        $this->current = $start;
        $destination = $this->checkDestination->validate($spacecraft, $x, $y);

        if ($start !== $destination) {
            $this->waypoints = $this->loadWaypoints->load($start, $destination);
            $this->isTraversing = true;
        }

        return $this;
    }

    #[Override]
    public function getCurrentWaypoint(): LocationInterface
    {
        return $this->current;
    }

    #[Override]
    public function getNextWaypoint(): LocationInterface
    {
        if ($this->waypoints->isEmpty()) {
            throw new RuntimeException('isDestinationArrived has to be called beforehand');
        }

        return $this->waypoints->first();
    }

    private function stepForward(): void
    {
        $first =  $this->waypoints->first();

        if (!$first) {
            return;
        }

        $this->current = $first;
        $this->waypoints->removeElement($this->current);
    }

    #[Override]
    public function abortFlight(): void
    {
        $this->waypoints->clear();
    }

    #[Override]
    public function enterNextWaypoint(
        FlightCompany $flightCompany,
        MessageCollectionInterface $messages
    ): void {

        $wrappers = $flightCompany->getActiveMembers();

        // flight consequences
        $this->walkWrappers(
            $wrappers,
            function (SpacecraftWrapperInterface $wrapper) use ($messages): void {
                $this->walkConsequences($this->flightConsequences, $wrapper, $messages);
            }
        );

        // enter waypoint
        $this->walkWrappers(
            $wrappers,
            function (SpacecraftWrapperInterface $wrapper): void {
                $this->enterWaypoint->enterNextWaypoint(
                    $wrapper->get(),
                    $this->isTraversing,
                    $this->getNextWaypoint(),
                    $this->wormholeEntry
                );
            }
        );

        $this->effectHandling->addFlightInformationForActiveEffects($this->getNextWaypoint(), $messages);

        // post flight consequences
        $this->walkWrappers(
            $wrappers,
            function (SpacecraftWrapperInterface $wrapper) use ($messages): void {
                $this->walkConsequences($this->postFlightConsequences, $wrapper, $messages);
            }
        );

        $this->stepForward();
    }

    /** @param Collection<int, SpacecraftWrapperInterface> $wrappers */
    private function walkWrappers(Collection $wrappers, callable $func): void
    {
        foreach ($wrappers as $wrapper) {
            $func($wrapper);
        }
    }

    /**
     * @param array<string, FlightConsequenceInterface> $consequences
     */
    private function walkConsequences(
        array $consequences,
        ?SpacecraftWrapperInterface $wrapper,
        MessageCollectionInterface $messages
    ): void {

        if ($wrapper === null) {
            return;
        }

        array_walk(
            $consequences,
            fn(FlightConsequenceInterface $consequence) => $consequence->trigger($wrapper, $this, $messages)
        );

        $this->walkConsequences($consequences, $wrapper->getTractoredShipWrapper(), $messages);
    }

    #[Override]
    public function isDestinationArrived(): bool
    {
        return $this->waypoints->isEmpty();
    }

    #[Override]
    public function getRouteMode(): RouteModeEnum
    {
        return $this->routeMode;
    }

    #[Override]
    public function isTraversing(): bool
    {
        return $this->isTraversing;
    }

    #[Override]
    public function isImpulseDriveNeeded(): bool
    {
        $routeMode = $this->routeMode;

        if (
            $routeMode === RouteModeEnum::SYSTEM_ENTRY
            || $routeMode === RouteModeEnum::WORMHOLE_ENTRY
        ) {
            return true;
        }

        return $routeMode === RouteModeEnum::FLIGHT
            && $this->getNextWaypoint() instanceof StarSystemMapInterface;
    }

    #[Override]
    public function isWarpDriveNeeded(): bool
    {
        $routeMode = $this->routeMode;

        if (
            $routeMode === RouteModeEnum::SYSTEM_EXIT
            || $routeMode === RouteModeEnum::TRANSWARP
        ) {
            return true;
        }

        return $routeMode === RouteModeEnum::FLIGHT
            && $this->getNextWaypoint() instanceof MapInterface;
    }

    #[Override]
    public function isTranswarpCoilNeeded(): bool
    {
        return $this->routeMode === RouteModeEnum::TRANSWARP;
    }

    #[Override]
    public function hasSpecialDamageOnField(): bool
    {
        foreach ($this->waypoints as $waypoint) {
            if ($waypoint->getFieldType()->getSpecialDamage() > 0) {
                return true;
            }
        }

        return false;
    }

    #[Override]
    public function hasEffectOnRoute(FieldTypeEffectEnum $effect): bool
    {
        foreach ($this->waypoints as $waypoint) {
            if ($waypoint->getFieldType()->hasEffect($effect)) {
                return true;
            }
        }

        return false;
    }

    #[Override]
    public function isDestinationInAdminRegion(array $regionIds): bool
    {
        $destination = $this->waypoints->last();

        return $destination instanceof MapInterface
            && in_array($destination->getAdminRegionId(), $regionIds);
    }

    #[Override]
    public function isDestinationAtTradepost(): bool
    {
        $destination = $this->waypoints->last();

        return $destination instanceof MapInterface
            && $destination->getSpacecrafts()->exists(fn(int $key, SpacecraftInterface $spacecraft): bool => $spacecraft instanceof StationInterface && $spacecraft->getTradePost() !== null);
    }
}
