<?php

namespace Stu\Module\Spacecraft\Lib\Movement;

use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RouteModeEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface ShipMovementInformationAdderInterface
{
    public function reachedDestination(
        SpacecraftInterface $spacecraft,
        bool $isFleetMode,
        RouteModeEnum $routeMode,
        MessageCollectionInterface $messages
    ): void;

    public function reachedDestinationDestroyed(
        SpacecraftInterface $spacecraft,
        string $leadShipName,
        bool $isFleetMode,
        RouteModeEnum $routeMode,
        MessageCollectionInterface $messages
    ): void;

    public function pulledTractoredShip(
        SpacecraftInterface $spacecraft,
        ShipInterface $tractoredShip,
        RouteModeEnum $routeMode,
        MessageCollectionInterface $messages
    ): void;
}
