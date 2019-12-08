<?php

namespace Stu\Orm\Entity;

interface PlanetTypeInterface
{
    public function getId(): int;

    public function getName(): string;

    public function setName(string $name): PlanetTypeInterface;

    public function getIsMoon(): bool;

    public function setIsMoon(bool $isMoon): PlanetTypeInterface;

    public function getDatabaseId(): ?int;

    public function setDatabaseId(?int $databaseId): PlanetTypeInterface;

    public function getColonizeableFields(): array;

    public function setColonizeableFields(array $colonizeableFields): PlanetTypeInterface;

    public function getBevGrowthRate(): int;

    public function setBevGrowthRate(int $bevGroethRate): PlanetTypeInterface;

    public function getSpecialId(): int;

    public function setSpecialId(int $specialId): PlanetTypeInterface;

    public function getAllowStart(): bool;

    public function setAllowStart(bool $allowStart): PlanetTypeInterface;

    public function hasRing(): bool;
}
