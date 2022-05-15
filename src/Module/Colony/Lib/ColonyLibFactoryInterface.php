<?php

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\UserInterface;

interface ColonyLibFactoryInterface
{
    public function createOrbitManagementShipItem(
        ShipInterface $ship,
        int $ownerUserId
    ): OrbitManagementShipItem;

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
        ?int $buildingId = null,
        bool $showUnderground = true
    ): ColonySurfaceInterface;

    public function createColonyListItem(
        ColonyInterface $colony
    ): ColonyListItemInterface;

    public function createBuildableRumpItem(
        ShipRumpInterface $shipRump,
        UserInterface $currentUser
    ): BuildableRumpListItemInterface;
}
