<?php

namespace Stu\Orm\Entity;

interface ColonyClassRestrictionInterface
{
    public function getId(): int;

    public function getColonyClass(): ColonyClassInterface;

    public function getTerraformingId(): ?int;

    public function setTerraformingId(?int $terraformingId): ColonyClassRestrictionInterface;

    public function getTerraforming(): ?TerraformingInterface;

    public function getBuildingId(): ?int;

    public function setBuildingId(?int $buildingId): ColonyClassRestrictionInterface;

    public function getBuilding(): ?BuildingInterface;
}
