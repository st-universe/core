<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Doctrine\Common\Collections\Collection;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\FlightCompany;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\WormholeEntryInterface;

interface FlightRouteInterface
{
    public function setDestination(
        MapInterface|StarSystemMapInterface $destination,
        bool $isTranswarp
    ): FlightRouteInterface;

    public function setDestinationViaWormhole(
        WormholeEntryInterface $wormholeEntry,
        bool $isEntry
    ): FlightRouteInterface;

    public function setDestinationViaCoordinates(
        ShipInterface $ship,
        int $x,
        int $y
    ): FlightRouteInterface;

    public function getCurrentWaypoint(): LocationInterface;

    public function getNextWaypoint(): LocationInterface;

    public function abortFlight(): void;

    public function enterNextWaypoint(
        FlightCompany $flightCompany,
        MessageCollectionInterface $messages
    ): void;

    public function isDestinationArrived(): bool;

    public function getRouteMode(): RouteModeEnum;

    public function isTraversing(): bool;

    public function isImpulseDriveNeeded(): bool;

    public function isWarpDriveNeeded(): bool;

    public function isTranswarpCoilNeeded(): bool;

    public function hasSpecialDamageOnField(): bool;

    public function hasEffectOnRoute(FieldTypeEffectEnum $effect): bool;

    /** @param array<int> $regionIds */
    public function isDestinationInAdminRegion(array $regionIds): bool;

    public function isDestinationAtTradepost(): bool;
}
