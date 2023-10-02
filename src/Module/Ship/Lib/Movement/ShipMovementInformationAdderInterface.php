<?php

namespace Stu\Module\Ship\Lib\Movement;

use Stu\Lib\InformationWrapper;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Orm\Entity\ShipInterface;

interface ShipMovementInformationAdderInterface
{
    public function reachedDestination(
        ShipInterface $ship,
        bool $isFleetMode,
        int $routeMode,
        MessageCollectionInterface $messages
    ): void;

    public function reachedDestinationDestroyed(
        ShipInterface $ship,
        string $leadShipName,
        bool $isFleetMode,
        int $routeMode,
        MessageCollectionInterface $messages
    ): void;

    public function pulledTractoredShip(
        ShipInterface $ship,
        ShipInterface $tractoredShip,
        int $routeMode,
        MessageCollectionInterface $messages
    ): void;
}
