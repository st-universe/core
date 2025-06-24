<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;

interface SpacecraftRumpInterface
{
    public function getId(): int;

    public function getBaseValues(): SpacecraftRumpBaseValuesInterface;

    public function getCategoryId(): SpacecraftRumpCategoryEnum;

    public function getRoleId(): ?SpacecraftRumpRoleEnum;

    public function getBeamFactor(): int;

    public function getShuttleSlots(): int;

    public function getTractorMass(): int;

    public function getTractorPayload(): int;

    public function getPhaserVolleys(): int;

    public function getPhaserHullDamageFactor(): int;

    public function getPhaserShieldDamageFactor(): int;

    public function getTorpedoLevel(): int;

    public function getTorpedoVolleys(): int;

    public function getBaseTorpedoStorage(): int;

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

    public function getPrestige(): int;

    public function getShipRumpRole(): ?ShipRumpRoleInterface;

    public function getShipRumpCategory(): ShipRumpCategoryInterface;

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

    public function getFactionId(): int;

    /**
     * @return Collection<int, ShipRumpSpecialInterface>
     */
    public function getSpecialAbilities(): Collection;

    public function getNeededRepairWorkbees(): ?int;

    public function getNpcBuildable(): ?bool;

    public function setNpcBuildable(?bool $npcBuildable): SpacecraftRumpInterface;
}
