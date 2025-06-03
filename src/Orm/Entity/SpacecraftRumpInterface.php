<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface SpacecraftRumpInterface
{
    public function getId(): int;

    public function setCategoryId(int $categoryId): SpacecraftRumpInterface;

    public function getCategoryId(): int;

    public function getRoleId(): ?int;

    public function setRoleId(?int $roleId): SpacecraftRumpInterface;

    public function getEvadeChance(): int;

    public function setEvadeChance(int $evadeChance): SpacecraftRumpInterface;

    public function getHitChance(): int;

    public function setHitChance(int $hitChance): SpacecraftRumpInterface;

    public function getModuleLevel(): int;

    public function setModuleLevel(int $moduleLevel): SpacecraftRumpInterface;

    public function getBaseCrew(): int;

    public function setBaseCrew(int $baseCrew): SpacecraftRumpInterface;

    public function getBaseEps(): int;

    public function setBaseEps(int $baseEps): SpacecraftRumpInterface;

    public function getBaseReactor(): int;

    public function setBaseReactor(int $baseReactor): SpacecraftRumpInterface;

    public function getBaseHull(): int;

    public function setBaseHull(int $baseHull): SpacecraftRumpInterface;

    public function getBaseShield(): int;

    public function setBaseShield(int $baseShield): SpacecraftRumpInterface;

    public function getBaseDamage(): int;

    public function getBaseSensorRange(): int;

    public function setBaseSensorRange(int $baseSensorRange): SpacecraftRumpInterface;

    public function getBaseTorpedoStorage(): int;

    public function setBaseTorpedoStorage(int $baseTorpedoStorage): SpacecraftRumpInterface;

    public function getBeamFactor(): int;

    public function setBeamFactor(int $beamFactor): SpacecraftRumpInterface;

    public function getSpecialSlots(): int;

    public function setSpecialSlots(int $specialSlots): SpacecraftRumpInterface;

    public function getShuttleSlots(): int;

    public function setShuttleSlots(int $shuttleSlots): SpacecraftRumpInterface;

    public function getTractorMass(): int;

    public function getTractorPayload(): int;

    public function getPhaserVolleys(): int;

    public function setPhaserVolleys(int $phaserVolleys): SpacecraftRumpInterface;

    public function getPhaserHullDamageFactor(): int;

    public function setPhaserHullDamageFactor(int $phaserHullDamageFactor): SpacecraftRumpInterface;

    public function getPhaserShieldDamageFactor(): int;

    public function setPhaserShieldDamageFactor(int $phaserShieldDamageFactor): SpacecraftRumpInterface;

    public function getTorpedoLevel(): int;

    public function setTorpedoLevel(int $torpedoLevel): SpacecraftRumpInterface;

    public function getTorpedoVolleys(): int;

    public function setTorpedoVolleys(int $torpedoVolleys): SpacecraftRumpInterface;

    public function getName(): string;

    public function setName(string $name): SpacecraftRumpInterface;

    public function getIsBuildable(): bool;

    public function setIsBuildable(bool $isBuildable): SpacecraftRumpInterface;

    public function getIsNpc(): bool;

    public function setIsNpc(bool $isNpc): SpacecraftRumpInterface;

    public function getEpsCost(): int;

    public function setEpsCost(int $energyCosts): SpacecraftRumpInterface;

    public function getStorage(): int;

    public function setStorage(int $storage): SpacecraftRumpInterface;

    public function getDockingSlots(): int;

    public function setDockingSlots(int $slots): SpacecraftRumpInterface;

    public function getBuildtime(): int;

    public function setBuildtime(int $buildtime): SpacecraftRumpInterface;

    public function getSort(): int;

    public function setSort(int $sort): SpacecraftRumpInterface;

    public function getDatabaseId(): ?int;

    public function getCommodityId(): ?int;

    public function setCommodityId(?int $commodityId): SpacecraftRumpInterface;

    public function getFlightEcost(): int;

    public function setFlightEcost(int $flightEcost): SpacecraftRumpInterface;

    public function getPrestige(): int;

    public function getShipRumpRole(): ?ShipRumpRoleInterface;

    public function setShipRumpRole(?ShipRumpRoleInterface $shipRumpRole): SpacecraftRumpInterface;

    public function getShipRumpCategory(): ShipRumpCategoryInterface;

    public function setShipRumpCategory(ShipRumpCategoryInterface $shipRumpCategory): SpacecraftRumpInterface;

    public function getCommodity(): ?CommodityInterface;

    public function setCommodity(?CommodityInterface $commodity): SpacecraftRumpInterface;

    public function getNeededWorkbees(): ?int;

    public function getDatabaseEntry(): ?DatabaseEntryInterface;

    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): SpacecraftRumpInterface;

    public function isEscapePods(): bool;

    public function isShipyard(): bool;

    public function isStation(): bool;

    public function isWorkbee(): bool;

    /**
     * @return Collection<int, ShipRumpCostInterface>
     */
    public function getBuildingCosts(): Collection;

    /**
     * @return Collection<int, BuildplanHangarInterface>
     */
    public function getStartHangar(): Collection;

    public function hasSpecialAbility(int $value): bool;

    public function getFractionId(): int;

    /**
     * @return Collection<int, ShipRumpSpecialInterface>
     */
    public function getSpecialAbilities(): Collection;

    public function getNeededRepairWorkbees(): ?int;

    public function getBaseWarpDrive(): int;

    public function setBaseWarpDrive(int $baseWarpDrive): SpacecraftRumpInterface;

    public function getNpcBuildable(): ?bool;

    public function setNpcBuildable(?bool $npcBuildable): SpacecraftRumpInterface;
}
