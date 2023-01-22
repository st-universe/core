<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface BuildingUpgradeInterface
{
    public function getId(): string;

    public function getUpgradeFromBuildingId(): int;

    public function setUpgradeFromBuildingId(int $upgradeFromBuildingId): BuildingUpgradeInterface;

    public function getUpgradeToBuildingId(): int;

    public function setUpgradeToBuildingId(int $upgradeToBuildingId): BuildingUpgradeInterface;

    public function getResearchId(): int;

    public function setResearchId(int $researchId): BuildingUpgradeInterface;

    public function getDescription(): string;

    public function setDescription(string $description): BuildingUpgradeInterface;

    public function getEnergyCost(): int;

    public function setEnergyCost(int $energyCost): BuildingUpgradeInterface;

    public function getBuilding(): BuildingInterface;

    /**
     * @return Collection<int, BuildingCostInterface>
     */
    public function getUpgradeCosts(): Collection;
}
