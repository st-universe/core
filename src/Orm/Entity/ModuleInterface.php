<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

interface ModuleInterface
{
    public function getId(): int;

    public function getName(): string;

    public function setName(string $name): ModuleInterface;

    public function getLevel(): int;

    public function setLevel(int $level): ModuleInterface;

    public function getUpgradeFactor(): int;

    public function setUpgradeFactor(int $upgradeFactor): ModuleInterface;

    public function getDefaultFactor(): int;

    public function setDefaultFactor(int $defaultFactor): ModuleInterface;

    public function getDowngradeFactor(): int;

    public function setDowngradeFactor(int $downgradeFactor): ModuleInterface;

    public function getCrew(): int;

    public function setCrew(int $crew): ModuleInterface;

    public function getCrewByFactionAndRumpLvl(FactionInterface $faction, SpacecraftRumpInterface $rump): int;

    public function getType(): SpacecraftModuleTypeEnum;

    public function setType(SpacecraftModuleTypeEnum $type): ModuleInterface;

    public function getResearchId(): ?int;

    public function setResearchId(int $researchId): ModuleInterface;

    public function getCommodityId(): int;

    public function setCommodityId(int $commodityId): ModuleInterface;

    public function getViewable(): bool;

    public function setViewable(bool $viewable): ModuleInterface;

    public function getShipRumpRoleId(): ?SpacecraftRumpRoleEnum;

    public function getWeapon(): ?WeaponInterface;

    public function getEcost(): int;

    public function setEcost(int $energyCosts): ModuleInterface;

    /**
     * @return Collection<int, ModuleSpecialInterface>
     */
    public function getSpecials(): Collection;

    public function hasSpecial(ModuleSpecialAbilityEnum $ability): bool;

    /**
     * @return Collection<int, ModuleCostInterface>
     */
    public function getCost(): Collection;

    /**
     * @return array<int, ModuleCostInterface>
     */
    public function getCostSorted(): array;

    public function getCommodity(): CommodityInterface;

    public function getDescription(): string;

    /**
     * @return Collection<int, TorpedoHullInterface>
     */
    public function getTorpedoHull(): Collection;

    /**
     * @return Collection<int, WeaponShieldInterface>
     */
    public function getWeaponShield(): Collection;

    public function getFaction(): ?FactionInterface;

    public function getFactionId(): ?int;

    public function setFactionId(int $factionId): ?ModuleInterface;

    public function getSystemType(): ?SpacecraftSystemTypeEnum;
}
