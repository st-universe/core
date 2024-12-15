<?php

namespace Stu\Orm\Entity;

interface ShipRumpColonizationBuildingInterface
{
    public function getId(): int;

    public function getRumpId(): int;

    public function getBuildingId(): int;

    public function setBuildingId(int $buildingId): ShipRumpColonizationBuildingInterface;
}
