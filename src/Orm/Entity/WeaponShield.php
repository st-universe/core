<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\WeaponShieldRepository;

#[Table(name: 'stu_weapon_shield')]
#[Index(name: 'weapon_shield_module_idx', columns: ['module_id'])]
#[Index(name: 'weapon_shield_weapon_idx', columns: ['weapon_id'])]
#[Entity(repositoryClass: WeaponShieldRepository::class)]
class WeaponShield
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $module_id = 0;

    #[Column(type: 'integer')]
    private int $weapon_id = 0;

    #[Column(type: 'integer')]
    private int $modificator = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $faction_id = 0;

    #[ManyToOne(targetEntity: Weapon::class)]
    #[JoinColumn(name: 'weapon_id', nullable: false, referencedColumnName: 'id')]
    private Weapon $weapon;

    #[ManyToOne(targetEntity: Module::class)]
    #[JoinColumn(name: 'module_id', nullable: false, referencedColumnName: 'id')]
    private Module $module;

    public function getId(): int
    {
        return $this->id;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }

    public function setModuleId(int $moduleId): WeaponShield
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getWeaponId(): int
    {
        return $this->weapon_id;
    }

    public function setWeaponId(int $weaponid): WeaponShield
    {
        $this->weapon_id = $weaponid;

        return $this;
    }

    public function getModificator(): int
    {
        return $this->modificator;
    }

    public function setModificator(int $Modificator): WeaponShield
    {
        $this->modificator = $Modificator;

        return $this;
    }

    public function getFactionId(): ?int
    {
        return $this->faction_id;
    }

    public function setFactionId(int $factionid): WeaponShield
    {
        $this->faction_id = $factionid;

        return $this;
    }

    public function getWeapon(): Weapon
    {
        return $this->weapon;
    }

    public function getModule(): Module
    {
        return $this->module;
    }
}
