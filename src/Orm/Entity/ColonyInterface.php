<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface ColonyInterface
{
    public function getId(): int;

    public function getColonyClassId(): int;

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

    public function setDatabaseId(?int $database_id): ColonyInterface;

    public function getPopulationlimit(): int;

    public function setPopulationlimit(int $populationlimit): ColonyInterface;

    public function getImmigrationstate(): bool;

    public function setImmigrationstate(bool $immigrationstate): ColonyInterface;

    public function getShields(): ?int;

    public function setShields(?int $shields): ColonyInterface;

    public function getTwilightZone(): int;

    public function getShieldFrequency(): ?int;

    public function setShieldFrequency(?int $shieldFrequency): ColonyInterface;

    public function getTorpedo(): ?TorpedoTypeInterface;

    public function setTorpedo(?TorpedoTypeInterface $torpedoType): ColonyInterface;

    public function getRotationFactor(): int;

    public function setRotationFactor(int $rotationFactor): ColonyInterface;

    public function getRotationTime(): int;

    public function getColonyTimeSeconds(): int;

    public function getColonyTimeHour(): ?string;

    public function getColonyTimeMinute(): ?string;

    public function getDayTimePrefix(): ?int;

    public function getDayTimeName(): ?string;

    public function getSurfaceWidth(): int;

    public function setSurfaceWidth(int $surfaceWidth): ColonyInterface;

    public function getColonyClass(): ColonyClassInterface;

    public function setColonyClass(ColonyClassInterface $colonyClass): ColonyInterface;

    public function getStorageSum(): int;

    public function storagePlaceLeft(): bool;

    public function isInSystem(): bool;

    public function getStarsystemMap(): ?StarSystemMapInterface;

    public function setStarsystemMap(StarSystemMapInterface $systemMap): ColonyInterface;

    public function getSystem(): StarSystemInterface;

    public function getBeamFactor(): int;

    /**
     * @return Collection<int, PlanetFieldInterface>
     */
    public function getPlanetFields(): Collection;

    /**
     * @return StorageInterface[]
     */
    public function getBeamableStorage(): array;

    /**
     * @return Collection<int, StorageInterface>
     */
    public function getStorage(): Collection;

    public function isDefended(): bool;

    /**
     * @return Collection<int, FleetInterface>
     */
    public function getDefenders(): Collection;

    public function isBlocked(): bool;

    /**
     * @return Collection<int, FleetInterface>
     */
    public function getBlockers(): Collection;

    /**
     * @return Collection<int, ShipCrewInterface>
     */
    public function getCrewAssignments(): Collection;

    public function getCrewAssignmentAmount(): int;

    public function getCrewTrainingAmount(): int;

    /**
     * @return ColonyDepositMiningInterface[]
     */
    public function getUserDepositMinings(): array;

    public function isFree(): bool;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): ColonyInterface;

    public function getPopulation(): int;

    public function getFreeHousing(): int;

    public function lowerEps(int $value): void;

    public function upperEps(int $value): void;

    public function getSectorString(): string;

    /**
     * @return Collection<int, ColonyDepositMiningInterface>
     */
    public function getDepositMinings(): Collection;
}
