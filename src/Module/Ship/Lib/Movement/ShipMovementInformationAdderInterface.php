<?php

namespace Stu\Module\Ship\Lib\Movement;

use Stu\Lib\InformationWrapper;
use Stu\Orm\Entity\ShipInterface;

interface ShipMovementInformationAdderInterface
{
    public function reachedDestination(
        ShipInterface $ship,
        bool $isFleetMode,
        int $routeMode,
        InformationWrapper $informations
    ): void;

    public function reachedDestinationDestroyed(
        ShipInterface $ship,
        bool $isFleetMode,
        int $routeMode,
        InformationWrapper $informations
    ): void;

    public function pulledTractoredShip(
        string $tractoredShipName,
        int $routeMode,
        InformationWrapper $informations
    ): void;

    public function notEnoughEnergyforTractoring(
        ShipInterface $ship,
        int $routeMode,
        InformationWrapper $informations
    ): void;
}
