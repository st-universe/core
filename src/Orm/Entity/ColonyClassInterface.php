<?php

namespace Stu\Orm\Entity;

interface ColonyClassInterface
{
    public function getId(): int;

    public function getName(): string;

    public function setName(string $name): ColonyClassInterface;

    public function getIsMoon(): bool;

    public function setIsMoon(bool $isMoon): ColonyClassInterface;

    public function getDatabaseId(): ?int;

    public function setDatabaseId(?int $databaseId): ColonyClassInterface;

    public function getColonizeableFields(): array;

    public function setColonizeableFields(array $colonizeableFields): ColonyClassInterface;

    public function getBevGrowthRate(): int;

    public function setBevGrowthRate(int $bevGroethRate): ColonyClassInterface;

    public function getSpecialId(): int;

    public function setSpecialId(int $specialId): ColonyClassInterface;

    public function getAllowStart(): bool;

    public function setAllowStart(bool $allowStart): ColonyClassInterface;

    public function hasRing(): bool;
}
