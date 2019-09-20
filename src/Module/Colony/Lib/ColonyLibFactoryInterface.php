<?php

namespace Stu\Module\Colony\Lib;

use ColonyData;
use ShipData;

interface ColonyLibFactoryInterface
{
    public function createOrbitShipItem(
        ShipData $ship,
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
        ColonyData $colony,
        ?int $buildingId = null
    ): ColonySurfaceInterface;
}