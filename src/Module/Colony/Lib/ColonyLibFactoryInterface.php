<?php

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

interface ColonyLibFactoryInterface
{
    public function createOrbitShipItem(
        ShipInterface $ship,
        int $ownerUserId
    ): OrbitShipItemInterface;

    public function createOrbitFleetItem(
        int $fleetId,
        array $shipList,
        int $ownerUserId
    ): OrbitFleetItemInterface;

    public function createBuildingFunctionTal(
        array $buildingFunctionIds
    ): BuildingFunctionTalInterface;

    public function createColonySurface(
        ColonyInterface $colony,
        ?int $buildingId = null
    ): ColonySurfaceInterface;

    public function createColonyListItem(
        ColonyInterface $colony
    ): ColonyListItemInterface;
}