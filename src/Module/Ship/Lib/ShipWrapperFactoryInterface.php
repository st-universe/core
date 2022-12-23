<?php

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;

interface ShipWrapperFactoryInterface
{
    public function wrapShip(ShipInterface $ship): ShipWrapperInterface;

    /**
     * @return ShipWrapperInterface[]
     */
    public function wrapShips(array $ships): array;

    /**
     * @param ShipInterface[] $ships
     */
    public function wrapShipsAsFleet(array $ships, bool $isSingleShips = false): FleetWrapperInterface;

    public function wrapFleet(FleetInterface $fleet): FleetWrapperInterface;

    /**
     * @return FleetWrapperInterface[]
     */
    public function wrapFleets(array $fleets): array;
}
