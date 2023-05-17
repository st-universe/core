<?php

namespace Stu\Orm\Entity;

interface WeaponShieldInterface
{
    public function getId(): int;

    public function getModuleId(): int;

    public function setModuleId(int $moduleId): WeaponShieldInterface;

    public function getWeaponId(): int;

    public function setWeaponId(int $weaponid): WeaponShieldInterface;

    public function getModificator(): int;

    public function setModificator(int $Modificator): WeaponShieldInterface;

    public function getFactionId(): ?int;

    public function setFactionId(int $factionid): WeaponShieldInterface;

    public function getWeapon(): WeaponInterface;

    public function getModule(): ModuleInterface;
}
