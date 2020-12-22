<?php

namespace Stu\Orm\Entity;

interface BuildingFieldAlternativeInterface
{
    public function getId(): int;

    public function getFieldType(): int;

    public function setFieldType(int $fieldType): BuildingFieldAlternativeInterface;

    public function getBuildingId(): int;

    public function setBuildingId(int $buildingId): BuildingFieldAlternativeInterface;

    public function getAlternativeBuildingId(): int;

    public function setAlternativeBuildingId(int $alternativeBuildingId): BuildingFieldAlternativeInterface;

    public function getAlternativeBuilding(): BuildingInterface;

    public function getResearchId(): ?int;

    public function setResearchId(?int $researchId): BuildingFieldAlternativeInterface;

    public function getBuilding(): BuildingInterface;
}