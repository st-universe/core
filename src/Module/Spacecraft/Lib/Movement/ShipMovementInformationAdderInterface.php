<?php

namespace Stu\Module\Spacecraft\Lib\Movement;

use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RouteModeEnum;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;

interface ShipMovementInformationAdderInterface
{
    public function reachedDestination(
        Spacecraft $spacecraft,
        bool $isFleetMode,
        RouteModeEnum $routeMode,
        MessageCollectionInterface $messages
    ): void;

    public function reachedDestinationDestroyed(
        Spacecraft $spacecraft,
        string $leadShipName,
        bool $isFleetMode,
        RouteModeEnum $routeMode,
        MessageCollectionInterface $messages
    ): void;

    public function pulledTractoredShip(
        Spacecraft $spacecraft,
        Ship $tractoredShip,
        RouteModeEnum $routeMode,
        MessageCollectionInterface $messages
    ): void;
}
