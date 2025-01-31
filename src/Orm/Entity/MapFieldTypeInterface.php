<?php

namespace Stu\Orm\Entity;

use Stu\Lib\Map\FieldTypeEffectEnum;

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

    public function getSpecialDamageType(): ?int;

    public function getView(): bool;

    public function setView(bool $view): MapFieldTypeInterface;

    public function getPassable(): bool;

    public function setPassable(bool $passable): MapFieldTypeInterface;

    public function getColonyClass(): ?ColonyClassInterface;

    /** @return array<FieldTypeEffectEnum> */
    public function getEffects(): ?array;

    /** @param null|array<FieldTypeEffectEnum> $consequences */
    public function setEffects(?array $consequences): MapFieldTypeInterface;

    public function getEffectsAsString(): ?string;
}
