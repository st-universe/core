<?php

namespace Stu\Module\Spacecraft\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftGroupInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StationInterface;

//TODO rename and move
interface SpacecraftWrapperFactoryInterface
{
    public function wrapSpacecraft(SpacecraftInterface $spacecraft): SpacecraftWrapperInterface;
    public function wrapShip(ShipInterface $ship): ShipWrapperInterface;
    public function wrapStation(StationInterface $station): StationWrapperInterface;

    /**
     * @param array<ShipInterface> $ships
     *
     * @return ArrayCollection<int, ShipWrapperInterface>
     */
    public function wrapShips(array $ships): Collection;

    /**
     * @param array<int, SpacecraftInterface> $spacecrafts
     *
     * @return Collection<int, SpacecraftWrapperInterface>
     */
    public function wrapSpacecrafts(array $spacecrafts): Collection;

    /**
     * @param Collection<int, covariant SpacecraftInterface> $spacecrafts
     * 
     * @return Collection<string, SpacecraftGroupInterface>
     */
    public function wrapSpacecraftsAsGroups(Collection $spacecrafts): Collection;

    public function wrapFleet(FleetInterface $fleet): FleetWrapperInterface;

    /**
     * @param array<FleetInterface> $fleets
     *
     * @return array<FleetWrapperInterface>
     */
    public function wrapFleets(array $fleets): array;
}
