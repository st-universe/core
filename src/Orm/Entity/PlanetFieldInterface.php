<?php

namespace Stu\Orm\Entity;

interface PlanetFieldInterface
{
    public function getId(): int;

    public function getColonyId(): int;

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

    public function getFieldTypeName(): string;

    public function getBuildtime(): int;

    public function isActive(): bool;

    public function isActivateable(): bool;

    public function hasHighDamage(): bool;

    public function isInConstruction(): bool;

    public function hasBuilding(): bool;

    public function getCssClass(): string;

    public function getBuildingState(): string;

    public function getBuilding(): ?BuildingInterface;

    public function setBuilding(?BuildingInterface $building): PlanetFieldInterface;

    public function isDamaged(): bool;

    public function clearBuilding(): void;

    public function getColony(): ColonyInterface;

    public function setColony(ColonyInterface $colony): PlanetFieldInterface;

    public function getTerraforming(): ?TerraformingInterface;

    public function setTerraforming(?TerraformingInterface $terraforming): PlanetFieldInterface;

    public function getNightPrefix(): string;

    public function getTerraformingState(): ?ColonyTerraformingInterface;

    public function getTerraformingOptions(): array;

    public function getTitleString(): string;

    public function getBuildProgress(): int;

    public function getOverlayWidth(): int;

    public function getPictureType(): string;

    public function getPossibleUpgrades(): array;

    public function isColonizeAble(): bool;

    public function isUnderground(): bool;

    public function hasUpgradeOrTerraformingOption(): bool;

    public function getConstructionStatusBar(): string;

    public function getTerraformingStatusBar(): string;
}
