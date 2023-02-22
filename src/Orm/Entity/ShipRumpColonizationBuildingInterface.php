<?php

namespace Stu\Orm\Entity;

interface ShipRumpColonizationBuildingInterface
{
    public function getId(): int;

    public function getRumpId(): int;

    public function setRumpId(int $shipRumpId): ShipRumpColonizationBuildingInterface;

    public function getBuildingId(): int;

    public function setBuildingId(int $buildingId): ShipRumpColonizationBuildingInterface;
}
