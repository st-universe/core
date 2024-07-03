<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\WeaponShieldRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_weapon_shield')]
#[Index(name: 'weapon_shield_module_idx', columns: ['module_id'])]
#[Index(name: 'weapon_shield_weapon_idx', columns: ['weapon_id'])]
#[Entity(repositoryClass: WeaponShieldRepository::class)]
class WeaponShield implements WeaponShieldInterface
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

    #[ManyToOne(targetEntity: 'Weapon')]
    #[JoinColumn(name: 'weapon_id', referencedColumnName: 'id')]
    private WeaponInterface $weapon;

    #[ManyToOne(targetEntity: 'Module')]
    #[JoinColumn(name: 'module_id', referencedColumnName: 'id')]
    private ModuleInterface $module;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getModuleId(): int
    {
        return $this->module_id;
    }

    #[Override]
    public function setModuleId(int $moduleId): WeaponShieldInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    #[Override]
    public function getWeaponId(): int
    {
        return $this->weapon_id;
    }

    #[Override]
    public function setWeaponId(int $weaponid): WeaponShieldInterface
    {
        $this->weapon_id = $weaponid;

        return $this;
    }

    #[Override]
    public function getModificator(): int
    {
        return $this->modificator;
    }

    #[Override]
    public function setModificator(int $Modificator): WeaponShieldInterface
    {
        $this->modificator = $Modificator;

        return $this;
    }

    #[Override]
    public function getFactionId(): ?int
    {
        return $this->faction_id;
    }

    #[Override]
    public function setFactionId(int $factionid): WeaponShieldInterface
    {
        $this->faction_id = $factionid;

        return $this;
    }

    #[Override]
    public function getWeapon(): WeaponInterface
    {
        return $this->weapon;
    }

    #[Override]
    public function getModule(): ModuleInterface
    {
        return $this->module;
    }
}
