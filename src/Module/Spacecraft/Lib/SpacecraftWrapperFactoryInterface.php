<?php

namespace Stu\Module\Spacecraft\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Station;

//TODO rename and move
interface SpacecraftWrapperFactoryInterface
{
    public function wrapSpacecraft(Spacecraft $spacecraft): SpacecraftWrapperInterface;
    public function wrapShip(Ship $ship): ShipWrapperInterface;
    public function wrapStation(Station $station): StationWrapperInterface;

    /**
     * @param array<Ship> $ships
     *
     * @return ArrayCollection<int, ShipWrapperInterface>
     */
    public function wrapShips(array $ships): Collection;

    /**
     * @param array<int, Spacecraft> $spacecrafts
     *
     * @return Collection<int, SpacecraftWrapperInterface>
     */
    public function wrapSpacecrafts(array $spacecrafts): Collection;

    /**
     * @param Collection<int, covariant Spacecraft> $spacecrafts
     *
     * @return Collection<string, SpacecraftGroupInterface>
     */
    public function wrapSpacecraftsAsGroups(Collection $spacecrafts): Collection;

    public function wrapFleet(Fleet $fleet): FleetWrapperInterface;

    /**
     * @param array<Fleet> $fleets
     *
     * @return array<FleetWrapperInterface>
     */
    public function wrapFleets(array $fleets): array;
}
