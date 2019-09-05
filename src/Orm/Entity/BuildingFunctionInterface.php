<?php

namespace Stu\Orm\Entity;

interface BuildingFunctionInterface
{
    public function getId(): int;

    public function getBuildingId(): int;

    public function setBuildingId(int $buildingId): BuildingFunctionInterface;

    public function getFunction(): int;

    public function setFunction(int $function): BuildingFunctionInterface;
}
