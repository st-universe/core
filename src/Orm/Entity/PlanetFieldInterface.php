<?php

namespace Stu\Orm\Entity;

interface PlanetFieldInterface
{
    public function getId(): int;

    public function getFieldId(): int;

    public function setFieldId(int $fieldId): PlanetFieldInterface;

    public function getFieldType(): int;

    public function setFieldType(int $planetFieldTypeId): PlanetFieldInterface;

    public function getBuildingId(): ?int;

    public function getTerraformingId(): ?int;

    public function getIntegrity(): int;

    public function setIntegrity(int $integrity): PlanetFieldInterface;

    public function getActive(): int;

    public function setActive(int $aktiv): PlanetFieldInterface;

    public function getActivateAfterBuild(): bool;

    public function setActivateAfterBuild(bool $activateAfterBuild): PlanetFieldInterface;

    public function setBuildMode(bool $value): void;

    public function getBuildtime(): int;

    public function isActive(): bool;

    public function isActivateable(): bool;

    public function hasHighDamage(): bool;

    public function isUnderConstruction(): bool;

    public function hasBuilding(): bool;

    public function getCssClass(): string;

    public function getBuildingState(): string;

    public function getBuilding(): ?BuildingInterface;

    public function setBuilding(?BuildingInterface $building): PlanetFieldInterface;

    public function isDamaged(): bool;

    public function clearBuilding(): void;

    public function getHost(): ColonyInterface|ColonySandboxInterface;

    public function setColony(ColonyInterface $colony): PlanetFieldInterface;

    public function setColonySandbox(ColonySandboxInterface $sandbox): PlanetFieldInterface;

    public function getTerraforming(): ?TerraformingInterface;

    public function setTerraforming(?TerraformingInterface $terraforming): PlanetFieldInterface;

    public function getDayNightPrefix(): string;

    public function getBuildProgress(): int;

    public function getOverlayWidth(): int;

    public function getPictureType(): string;

    public function isColonizeAble(): bool;
}
