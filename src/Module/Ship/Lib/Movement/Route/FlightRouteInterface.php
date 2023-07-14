<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Stu\Lib\InformationWrapper;
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

    public function getNextWaypoint(): MapInterface|StarSystemMapInterface;

    public function stepForward(): void;

    public function abortFlight(): void;

    public function enterNextWaypoint(
        ShipInterface $ship,
        InformationWrapper $informations
    ): void;

    public function isDestinationArrived(): bool;
}
