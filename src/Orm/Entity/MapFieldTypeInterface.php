<?php

namespace Stu\Orm\Entity;

interface MapFieldTypeInterface
{
    public function getId(): int;

    public function getType(): int;

    public function setType(int $type): MapFieldTypeInterface;

    public function getIsSystem(): bool;

    public function setIsSystem(bool $isSystem): MapFieldTypeInterface;

    public function getEnergyCosts(): int;

    public function setEnergyCosts(int $energyCosts): MapFieldTypeInterface;

    public function getName(): string;

    public function setName(string $name): MapFieldTypeInterface;

    public function getColonyClassId(): int;

    public function setColonyClassId(int $colonyClassId): MapFieldTypeInterface;

    public function getDamage(): int;

    public function setDamage(int $damage): MapFieldTypeInterface;

    public function getSpecialDamage(): int;

    public function setSpecialDamage(int $specialDamage): MapFieldTypeInterface;

    public function getSpecialDamageInnerSystem(): int;

    public function setSpecialDamageInnerSystem(int $specialDamageInnerSystem): MapFieldTypeInterface;

    public function getView(): bool;

    public function setView(bool $view): MapFieldTypeInterface;

    public function getPassable(): bool;

    public function setPassable(bool $passable): MapFieldTypeInterface;

    public function getPassableAsInt(): int;

    public function getColonyClass(): ?ColonyClassInterface;
}
