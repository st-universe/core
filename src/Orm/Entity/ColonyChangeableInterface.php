<?php

namespace Stu\Orm\Entity;

interface ColonyChangeableInterface
{
    public function getColony(): ColonyInterface;

    public function getWorkers(): int;

    public function setWorkers(int $bev_work): ColonyChangeableInterface;

    public function getWorkless(): int;

    public function setWorkless(int $bev_free): ColonyChangeableInterface;

    public function getMaxBev(): int;

    public function setMaxBev(int $bev_max): ColonyChangeableInterface;

    public function getEps(): int;

    public function setEps(int $eps): ColonyChangeableInterface;

    public function getMaxEps(): int;

    public function setMaxEps(int $max_eps): ColonyChangeableInterface;

    public function getMaxStorage(): int;

    public function setMaxStorage(int $max_storage): ColonyChangeableInterface;

    public function getPopulation(): int;

    public function getPopulationlimit(): int;

    public function setPopulationlimit(int $populationlimit): ColonyChangeableInterface;

    public function getImmigrationstate(): bool;

    public function setImmigrationstate(bool $immigrationstate): ColonyChangeableInterface;

    public function getShields(): ?int;

    public function setShields(?int $shields): ColonyChangeableInterface;

    public function getShieldFrequency(): ?int;

    public function setShieldFrequency(?int $shieldFrequency): ColonyChangeableInterface;

    public function getTorpedo(): ?TorpedoTypeInterface;

    public function setTorpedo(?TorpedoTypeInterface $torpedoType): ColonyChangeableInterface;

    public function getFreeHousing(): int;

    public function lowerEps(int $value): void;

    public function upperEps(int $value): void;
}
