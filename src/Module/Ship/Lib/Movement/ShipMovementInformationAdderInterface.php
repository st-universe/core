<?php

namespace Stu\Module\Ship\Lib\Movement;

use Stu\Lib\InformationWrapper;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Orm\Entity\ShipInterface;

interface ShipMovementInformationAdderInterface
{
    public function reachedDestination(
        ShipInterface $ship,
        bool $isFleetMode,
        int $routeMode,
        FightMessageCollectionInterface $messages
    ): void;

    public function reachedDestinationDestroyed(
        ShipInterface $ship,
        string $leadShipName,
        bool $isFleetMode,
        int $routeMode,
        FightMessageCollectionInterface $messages
    ): void;

    public function pulledTractoredShip(
        ShipInterface $ship,
        ShipInterface $tractoredShip,
        int $routeMode,
        FightMessageCollectionInterface $messages
    ): void;
}
