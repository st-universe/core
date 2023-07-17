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

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\WeaponShieldRepository")
 * @Table(
 *     name="stu_weapon_shield",
 *     indexes={
 *         @Index(name="weapon_shield_module_idx", columns={"module_id"}),
 *         @Index(name="weapon_shield_weapon_idx", columns={"weapon_id"})
 *     }
 * )
 **/
class WeaponShield implements WeaponShieldInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="integer")
     *
     */
    private int $module_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $weapon_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $modificator = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $faction_id = 0;

    /**
     * @ManyToOne(targetEntity="Weapon")
     * @JoinColumn(name="weapon_id", referencedColumnName="id")
     */
    private WeaponInterface $weapon;

    /**
     * @ManyToOne(targetEntity="Module")
     * @JoinColumn(name="module_id", referencedColumnName="id")
     */
    private ModuleInterface $module;

    public function getId(): int
    {
        return $this->id;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }

    public function setModuleId(int $moduleId): WeaponShieldInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getWeaponId(): int
    {
        return $this->weapon_id;
    }

    public function setWeaponId(int $weaponid): WeaponShieldInterface
    {
        $this->weapon_id = $weaponid;

        return $this;
    }

    public function getModificator(): int
    {
        return $this->modificator;
    }

    public function setModificator(int $Modificator): WeaponShieldInterface
    {
        $this->modificator = $Modificator;

        return $this;
    }

    public function getFactionId(): ?int
    {
        return $this->faction_id;
    }

    public function setFactionId(int $factionid): WeaponShieldInterface
    {
        $this->faction_id = $factionid;

        return $this;
    }

    public function getWeapon(): WeaponInterface
    {
        return $this->weapon;
    }

    public function getModule(): ModuleInterface
    {
        return $this->module;
    }
}
