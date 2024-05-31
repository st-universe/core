<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface ShipRumpInterface
{
    public function getId(): int;

    public function setCategoryId(int $categoryId): ShipRumpInterface;

    public function getCategoryId(): int;

    public function getRoleId(): ?int;

    public function setRoleId(?int $roleId): ShipRumpInterface;

    public function getEvadeChance(): int;

    public function setEvadeChance(int $evadeChance): ShipRumpInterface;

    public function getHitChance(): int;

    public function setHitChance(int $hitChance): ShipRumpInterface;

    public function getModuleLevel(): int;

    public function setModuleLevel(int $moduleLevel): ShipRumpInterface;

    public function getBaseCrew(): int;

    public function setBaseCrew(int $baseCrew): ShipRumpInterface;

    public function getBaseEps(): int;

    public function setBaseEps(int $baseEps): ShipRumpInterface;

    public function getBaseReactor(): int;

    public function setBaseReactor(int $baseReactor): ShipRumpInterface;

    public function getBaseHull(): int;

    public function setBaseHull(int $baseHull): ShipRumpInterface;

    public function getBaseShield(): int;

    public function setBaseShield(int $baseShield): ShipRumpInterface;

    public function getBaseDamage(): int;

    public function setBaseDamage(int $baseDamage): ShipRumpInterface;

    public function getBaseSensorRange(): int;

    public function setBaseSensorRange(int $baseSensorRange): ShipRumpInterface;

    public function getBaseTorpedoStorage(): int;

    public function setBaseTorpedoStorage(int $baseTorpedoStorage): ShipRumpInterface;

    public function getBeamFactor(): int;

    public function setBeamFactor(int $beamFactor): ShipRumpInterface;

    public function getSpecialSlots(): int;

    public function setSpecialSlots(int $specialSlots): ShipRumpInterface;

    public function getShuttleSlots(): int;

    public function setShuttleSlots(int $shuttleSlots): ShipRumpInterface;

    public function getTractorMass(): int;

    public function getTractorPayload(): int;

    public function getPhaserVolleys(): int;

    public function setPhaserVolleys(int $phaserVolleys): ShipRumpInterface;

    public function getPhaserHullDamageFactor(): int;

    public function setPhaserHullDamageFactor(int $phaserHullDamageFactor): ShipRumpInterface;

    public function getPhaserShieldDamageFactor(): int;

    public function setPhaserShieldDamageFactor(int $phaserShieldDamageFactor): ShipRumpInterface;

    public function getTorpedoLevel(): int;

    public function setTorpedoLevel(int $torpedoLevel): ShipRumpInterface;

    public function getTorpedoVolleys(): int;

    public function setTorpedoVolleys(int $torpedoVolleys): ShipRumpInterface;

    public function getName(): string;

    public function setName(string $name): ShipRumpInterface;

    public function getIsBuildable(): bool;

    public function setIsBuildable(bool $isBuildable): ShipRumpInterface;

    public function getIsNpc(): bool;

    public function setIsNpc(bool $isNpc): ShipRumpInterface;

    public function getEpsCost(): int;

    public function setEpsCost(int $energyCosts): ShipRumpInterface;

    public function getStorage(): int;

    public function setStorage(int $storage): ShipRumpInterface;

    public function getDockingSlots(): int;

    public function setDockingSlots(int $slots): ShipRumpInterface;

    public function getBuildtime(): int;

    public function setBuildtime(int $buildtime): ShipRumpInterface;

    public function getSort(): int;

    public function setSort(int $sort): ShipRumpInterface;

    public function getDatabaseId(): ?int;

    public function setDatabaseId(?int $databaseId): ShipRumpInterface;

    public function getCommodityId(): ?int;

    public function setCommodityId(?int $commodityId): ShipRumpInterface;

    public function getFlightEcost(): int;

    public function setFlightEcost(int $flightEcost): ShipRumpInterface;

    public function getPrestige(): int;

    public function getShipRumpRole(): ?ShipRumpRoleInterface;

    public function setShipRumpRole(?ShipRumpRoleInterface $shipRumpRole): ShipRumpInterface;

    public function getShipRumpCategory(): ShipRumpCategoryInterface;

    public function setShipRumpCategory(ShipRumpCategoryInterface $shipRumpCategory): ShipRumpInterface;

    public function getCommodity(): ?CommodityInterface;

    public function setCommodity(?CommodityInterface $commodity): ShipRumpInterface;

    public function getNeededWorkbees(): ?int;

    public function getDatabaseEntry(): ?DatabaseEntryInterface;

    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): ShipRumpInterface;

    public function isTrumfield(): bool;

    public function isEscapePods(): bool;

    public function isShipyard(): bool;

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

    public function setBaseWarpDrive(int $baseWarpDrive): ShipRumpInterface;
}
