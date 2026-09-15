<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_rump_base_values')]
#[Entity]
class SpacecraftRumpBaseValues
{
    #[Id]
    #[OneToOne(targetEntity: SpacecraftRump::class, inversedBy: 'baseValues')]
    #[JoinColumn(name: 'rump_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private SpacecraftRump $rump;

    #[Column(type: 'smallint')]
    private int $evade_chance = 0;

    #[Column(type: 'smallint')]
    private int $hit_chance = 0;

    #[Column(type: 'smallint')]
    private int $module_level = 0;

    #[Column(type: 'smallint')]
    private int $base_crew = 0;

    #[Column(type: 'smallint')]
    private int $max_crew = 0;

    #[Column(type: 'smallint')]
    private int $base_eps = 0;

    #[Column(type: 'smallint')]
    private int $base_reactor = 0;

    #[Column(type: 'integer')]
    private int $base_hull = 0;

    #[Column(type: 'integer')]
    private int $base_shield = 0;

    #[Column(type: 'smallint')]
    private int $base_damage = 0;

    #[Column(type: 'smallint')]
    private int $base_sensor_range = 0;

    #[Column(type: 'integer')]
    private int $base_warpdrive = 0;

    #[Column(type: 'smallint')]
    private int $special_slots = 0;

    public function setRump(SpacecraftRump $rump): SpacecraftRumpBaseValues
    {
        $this->rump = $rump;

        return $this;
    }

    public function getRump(): SpacecraftRump
    {
        return $this->rump;
    }

    public function getEvadeChance(): int
    {
        return $this->evade_chance;
    }

    public function setEvadeChance(int $evadeChance): SpacecraftRumpBaseValues
    {
        $this->evade_chance = $evadeChance;

        return $this;
    }

    public function getHitChance(): int
    {
        return $this->hit_chance;
    }

    public function setHitChance(int $hitChance): SpacecraftRumpBaseValues
    {
        $this->hit_chance = $hitChance;

        return $this;
    }

    public function getModuleLevel(): int
    {
        return $this->module_level;
    }

    public function setModuleLevel(int $moduleLevel): SpacecraftRumpBaseValues
    {
        $this->module_level = $moduleLevel;

        return $this;
    }

    public function getBaseCrew(): int
    {
        return $this->base_crew;
    }

    public function setBaseCrew(int $baseCrew): SpacecraftRumpBaseValues
    {
        $this->base_crew = $baseCrew;

        return $this;
    }

    public function getMaxCrew(): int
    {
        return $this->max_crew;
    }

    public function setMaxCrew(int $maxCrew): SpacecraftRumpBaseValues
    {
        $this->max_crew = $maxCrew;

        return $this;
    }

    public function getBaseEps(): int
    {
        return $this->base_eps;
    }

    public function setBaseEps(int $baseEps): SpacecraftRumpBaseValues
    {
        $this->base_eps = $baseEps;

        return $this;
    }

    public function getBaseReactor(): int
    {
        return $this->base_reactor;
    }

    public function setBaseReactor(int $baseReactor): SpacecraftRumpBaseValues
    {
        $this->base_reactor = $baseReactor;

        return $this;
    }

    public function getBaseHull(): int
    {
        return $this->base_hull;
    }

    public function setBaseHull(int $baseHull): SpacecraftRumpBaseValues
    {
        $this->base_hull = $baseHull;

        return $this;
    }

    public function getBaseShield(): int
    {
        return $this->base_shield;
    }

    public function setBaseShield(int $baseShield): SpacecraftRumpBaseValues
    {
        $this->base_shield = $baseShield;

        return $this;
    }

    public function getBaseDamage(): int
    {
        return $this->base_damage;
    }

    public function setBaseDamage(int $baseDamage): SpacecraftRumpBaseValues
    {
        $this->base_damage = $baseDamage;

        return $this;
    }

    public function getBaseSensorRange(): int
    {
        return $this->base_sensor_range;
    }

    public function setBaseSensorRange(int $baseSensorRange): SpacecraftRumpBaseValues
    {
        $this->base_sensor_range = $baseSensorRange;

        return $this;
    }

    public function getBaseWarpDrive(): int
    {
        return $this->base_warpdrive;
    }

    public function setBaseWarpDrive(int $baseWarpDrive): SpacecraftRumpBaseValues
    {
        $this->base_warpdrive = $baseWarpDrive;

        return $this;
    }

    public function getSpecialSlots(): int
    {
        return $this->special_slots;
    }

    public function setSpecialSlots(int $specialSlots): SpacecraftRumpBaseValues
    {
        $this->special_slots = $specialSlots;

        return $this;
    }
}
