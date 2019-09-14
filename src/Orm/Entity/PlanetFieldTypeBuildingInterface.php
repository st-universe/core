<?php

namespace Stu\Orm\Entity;

interface PlanetFieldTypeBuildingInterface
{
    public function getId(): int;

    public function getFieldTypeId(): int;

    public function setFieldTypeId(int $fieldTypeId): PlanetFieldTypeBuildingInterface;

    public function getBuildingId(): int;

    public function setBuildingId(int $buildingId): PlanetFieldTypeBuildingInterface;
}