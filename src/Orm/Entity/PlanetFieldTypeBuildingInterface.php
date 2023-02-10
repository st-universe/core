<?php

namespace Stu\Orm\Entity;

interface PlanetFieldTypeBuildingInterface
{
    public function getId(): int;

    public function getFieldTypeId(): int;

    public function setFieldTypeId(int $fieldTypeId): PlanetFieldTypeBuildingInterface;

    public function getBuildingId(): int;

    public function setBuildingId(int $buildingId): PlanetFieldTypeBuildingInterface;

    public function getResearchId(): ?int;

    public function setResearchId(?int $researchId): PlanetFieldTypeBuildingInterface;

    public function getView(): bool;

    public function setView(bool $view): PlanetFieldTypeBuildingInterface;
}
