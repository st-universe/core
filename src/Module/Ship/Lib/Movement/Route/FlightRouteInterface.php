<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\WormholeEntryInterface;

interface FlightRouteInterface
{
    public function setDestination(MapInterface|StarSystemMapInterface $destination): FlightRouteInterface;

    public function setDestinationViaWormhole(
        WormholeEntryInterface $wormholeEntry,
        bool $isEntry
    ): FlightRouteInterface;

    public function setDestinationViaCoordinates(
        ShipInterface $ship,
        int $x,
        int $y
    ): FlightRouteInterface;

    public function getCurrentWaypoint(): MapInterface|StarSystemMapInterface;

    public function getNextWaypoint(): MapInterface|StarSystemMapInterface;

    public function stepForward(): void;

    public function abortFlight(): void;

    public function enterNextWaypoint(
        ShipWrapperInterface $wrapper,
        FightMessageCollectionInterface $messages
    ): void;

    public function isDestinationArrived(): bool;

    public function getRouteMode(): int;

    public function isTraversing(): bool;

    public function isImpulseDriveNeeded(): bool;

    public function isWarpDriveNeeded(): bool;

    public function isTranswarpCoilNeeded(): bool;
}
