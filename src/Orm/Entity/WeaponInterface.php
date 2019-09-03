<?php

namespace Stu\Orm\Entity;

interface WeaponInterface
{
    public function getId(): int;

    public function getName(): string;

    public function setName(string $name): WeaponInterface;

    public function getVariance(): int;

    public function setVariance(int $variance): WeaponInterface;

    public function getCriticalChance(): int;

    public function setCriticalChance(int $criticalChance): WeaponInterface;

    public function getType(): int;

    public function setType(int $type): WeaponInterface;

    public function getFiringMode(): int;

    public function setFiringMode(int $firingMode): WeaponInterface;

    public function getModuleId(): int;

    public function setModuleId(int $moduleId): WeaponInterface;
}