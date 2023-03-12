<?php

namespace Stu\Orm\Entity;

interface ShipRumpBuildingFunctionInterface
{
    public function getId(): int;

    public function getShipRumpId(): int;

    public function setShipRumpId(int $shipRumpId): ShipRumpBuildingFunctionInterface;

    public function getBuildingFunction(): int;

    public function setBuildingFunction(int $buildingFunction): ShipRumpBuildingFunctionInterface;
}