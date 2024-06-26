<?php

namespace Stu\Module\Ship\Lib\Movement;

use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Route\RouteModeEnum;
use Stu\Orm\Entity\ShipInterface;

interface ShipMovementInformationAdderInterface
{
    public function reachedDestination(
        ShipInterface $ship,
        bool $isFleetMode,
        RouteModeEnum $routeMode,
        MessageCollectionInterface $messages
    ): void;

    public function reachedDestinationDestroyed(
        ShipInterface $ship,
        string $leadShipName,
        bool $isFleetMode,
        RouteModeEnum $routeMode,
        MessageCollectionInterface $messages
    ): void;

    public function pulledTractoredShip(
        ShipInterface $ship,
        ShipInterface $tractoredShip,
        RouteModeEnum $routeMode,
        MessageCollectionInterface $messages
    ): void;
}
