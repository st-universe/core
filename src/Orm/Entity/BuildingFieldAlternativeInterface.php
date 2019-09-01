<?php

namespace Stu\Orm\Entity;

use BuildingData;

interface BuildingFieldAlternativeInterface
{
    public function getId(): int;

    public function getFieldType(): int;

    public function setFieldType(int $fieldType): BuildingFieldAlternativeInterface;

    public function getBuildingId(): int;

    public function setBuildingId(int $buildingId): BuildingFieldAlternativeInterface;

    public function getAlternativeBuildingId(): int;

    public function setAlternativeBuildingId(int $alternativeBuildingId): BuildingFieldAlternativeInterface;

    public function getAlternativeBuilding(): BuildingData;

    public function getBuilding(): BuildingData;
}