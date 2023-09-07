<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface ColonyClassInterface
{
    public function getId(): int;

    public function getName(): string;

    public function setName(string $name): ColonyClassInterface;

    public function getType(): int;

    public function isPlanet(): bool;

    public function isMoon(): bool;

    public function isAsteroid(): bool;

    public function getDatabaseId(): ?int;

    public function setDatabaseId(?int $databaseId): ColonyClassInterface;

    /**
     * @return array<int>
     */
    public function getColonizeableFields(): array;

    public function setColonizeableFields(array $colonizeableFields): ColonyClassInterface;

    public function getBevGrowthRate(): int;

    public function setBevGrowthRate(int $bevGroethRate): ColonyClassInterface;

    public function getSpecialId(): int;

    public function setSpecialId(int $specialId): ColonyClassInterface;

    public function getAllowStart(): bool;

    public function setAllowStart(bool $allowStart): ColonyClassInterface;

    /**
     * @return Collection<int, ColonyClassDepositInterface>
     */
    public function getColonyClassDeposits(): Collection;

    public function hasRing(): bool;

    public function getMinRotation(): int;

    public function setMinRotation(int $rotation): ColonyClassInterface;

    public function getMaxRotation(): int;

    public function setMaxRotation(int $rotation): ColonyClassInterface;
}
