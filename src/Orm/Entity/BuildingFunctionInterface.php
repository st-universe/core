<?php

namespace Stu\Orm\Entity;

use Stu\Component\Building\BuildingFunctionEnum;

interface BuildingFunctionInterface
{
    public function getId(): int;

    public function getBuildingId(): int;

    public function setBuildingId(int $buildingId): BuildingFunctionInterface;

    public function getFunction(): BuildingFunctionEnum;

    public function setFunction(BuildingFunctionEnum $function): BuildingFunctionInterface;
}
