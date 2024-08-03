<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;

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

    public function getCrewByFactionAndRumpLvl(FactionInterface $faction, ShipRumpInterface $rump): int;

    public function getType(): ShipModuleTypeEnum;

    public function setType(ShipModuleTypeEnum $type): ModuleInterface;

    public function getResearchId(): ?int;

    public function setResearchId(int $researchId): ModuleInterface;

    public function getCommodityId(): int;

    public function setCommodityId(int $commodityId): ModuleInterface;

    public function getViewable(): bool;

    public function setViewable(bool $viewable): ModuleInterface;

    public function getShipRumpRoleId(): ?int;

    public function setShipRumpRoleId(int $shipRumpRoleId): ModuleInterface;

    public function getWeapon(): ?WeaponInterface;

    public function getEcost(): int;

    public function setEcost(int $energyCosts): ModuleInterface;

    public function hasSpecial(int $special_id): bool;

    /**
     * @return Collection<int, ModuleSpecialInterface>
     */
    public function getSpecials(): Collection;

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

    public function getSystemType(): ?ShipSystemTypeEnum;
}
