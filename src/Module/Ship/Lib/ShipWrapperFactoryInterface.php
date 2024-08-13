<?php

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;

interface ShipWrapperFactoryInterface
{
    public function wrapShip(ShipInterface $ship): ShipWrapperInterface;

    /**
     * @param array<ShipInterface> $ships
     *
     * @return ArrayCollection<int, ShipWrapperInterface>
     */
    public function wrapShips(array $ships): Collection;

    /**
     * @param array<ShipInterface> $ships
     */
    public function wrapShipsAsFleet(array $ships, bool $isSingleShips = false): FleetWrapperInterface;

    public function wrapFleet(FleetInterface $fleet): FleetWrapperInterface;

    /**
     * @param array<FleetInterface> $fleets
     *
     * @return array<FleetWrapperInterface>
     */
    public function wrapFleets(array $fleets): array;
}
