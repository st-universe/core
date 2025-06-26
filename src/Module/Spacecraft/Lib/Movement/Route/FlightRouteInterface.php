<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Doctrine\Common\Collections\Collection;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\FlightCompany;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\WormholeEntry;

interface FlightRouteInterface
{
    public function setDestination(
        Map|StarSystemMap $destination,
        bool $isTranswarp
    ): FlightRouteInterface;

    public function setDestinationViaWormhole(
        WormholeEntry $wormholeEntry,
        bool $isEntry
    ): FlightRouteInterface;

    public function setDestinationViaCoordinates(
        Ship $ship,
        int $x,
        int $y
    ): FlightRouteInterface;

    public function getCurrentWaypoint(): Location;

    public function getNextWaypoint(): Location;

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
