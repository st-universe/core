<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;
use Stu\Lib\ColonyProduction\ColonyProduction;

interface ColonyInterface
{
    public function getId(): int;

    public function getColonyClass(): int;

    public function getUserId(): int;

    public function getSx(): int;

    public function getSy(): int;

    public function getSystemsId(): int;

    public function getName(): string;

    public function getNameAndSectorString(): string;

    public function setName(string $name): ColonyInterface;

    public function getPlanetName(): string;

    public function setPlanetName(string $planet_name): ColonyInterface;

    public function getWorkers(): int;

    public function setWorkers(int $bev_work): ColonyInterface;

    public function getWorkless(): int;

    public function setWorkless(int $bev_free): ColonyInterface;

    public function getMaxBev(): int;

    public function setMaxBev(int $bev_max): ColonyInterface;

    public function getEps(): int;

    public function setEps(int $eps): ColonyInterface;

    public function getMaxEps(): int;

    public function setMaxEps(int $max_eps): ColonyInterface;

    public function getMaxStorage(): int;

    public function setMaxStorage(int $max_storage): ColonyInterface;

    public function getMask(): ?string;

    public function setMask(?string $mask): ColonyInterface;

    public function getDatabaseId(): ?int;

    public function setDatabaseId(?int $database_id);

    public function getPopulationlimit(): int;

    public function setPopulationlimit(int $populationlimit): ColonyInterface;

    public function getImmigrationstate(): bool;

    public function setImmigrationstate(bool $immigrationstate): ColonyInterface;

    public function getShields(): ?int;

    public function setShields(?int $shields): ColonyInterface;

    public function getMaxShields(): int;

    public function getTwilightZone(): int;

    public function hasShields(): bool;

    public function getShieldState(): bool;

    public function getShieldFrequency(): ?int;

    public function setShieldFrequency(?int $shieldFrequency): ColonyInterface;

    public function getTorpedo(): ?TorpedoTypeInterface;

    public function setTorpedo(?TorpedoTypeInterface $torpedoType): ColonyInterface;

    public function getRotationFactor(): int;

    public function getRotationTime(): int;

    public function getColonyTimeSeconds(): int;

    public function getColonyTimeHour(): ?string;

    public function getColonyTimeMinute(): ?string;

    public function getDayTimePrefix(): ?int;

    public function getDayTimeName(): ?string;

    public function getSurfaceWidth(): ?int;

    public function setSurfaceWidth(int $surfaceWidth): ColonyInterface;

    public function getPlanetType(): PlanetTypeInterface;

    public function setPlanetType(PlanetTypeInterface $planetType): ColonyInterface;

    public function getStorageSum(): int;

    public function storagePlaceLeft(): bool;

    public function isInSystem(): bool;

    public function getStarsystemMap(): ?StarSystemMapInterface;

    public function getSystem(): StarSystemInterface;

    public function getEpsProduction(): int;

    public function getBeamFactor(): int;

    public function getPlanetFields(): Collection;

    /**
     * @return StorageInterface[]
     */
    public function getBeamableStorage(): array;

    /**
     * @return StorageInterface[]
     */
    public function getStorage(): Collection;

    public function isDefended(): bool;

    /**
     * @return FleetInterface[]
     */
    public function getDefenders(): Collection;

    public function isBlocked(): bool;

    /**
     * @return FleetInterface[]
     */
    public function getBlockers(): Collection;

    /**
     * @return ColonyProduction[]
     */
    public function getProductionRaw(): array;

    public function setProductionRaw(array $array): void;

    /**
     * @return ColonyProduction[]
     */
    public function getProduction(): array;

    public function getProductionSum(): int;

    public function getOrbitShipList(int $userId): array;

    public function isFree(): bool;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): ColonyInterface;

    public function getBevFood(): int;

    public function getPopulation(): int;

    public function getFreeHousing(): int;

    public function getImmigration(): int;

    public function getNegativeEffect(): int;

    public function getPositiveEffectPrimary(): int;

    public function getPositiveEffectSecondary(): int;

    public function getCrewLimit(): int;

    public function clearCache(): void;

    public function hasActiveBuildingWithFunction(int $function_id): bool;

    public function hasBuildingWithFunction(int $function_id, array $states = [1]): bool;

    public function getBuildingWithFunctionCount(int $function_id, array $states = [1]): int;

    public function lowerEps(int $value): void;

    public function upperEps(int $value): void;

    public function getSectorString(): string;
}