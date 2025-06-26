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
    #[JoinColumn(name: 'rump_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftRump $rump;

    #[column(type: 'smallint')]
    private int $evade_chance = 0;

    #[column(type: 'smallint')]
    private int $hit_chance = 0;

    #[column(type: 'smallint')]
    private int $module_level = 0;

    #[column(type: 'smallint')]
    private int $base_crew = 0;

    #[column(type: 'smallint')]
    private int $base_eps = 0;

    #[column(type: 'smallint')]
    private int $base_reactor = 0;

    #[column(type: 'integer')]
    private int $base_hull = 0;

    #[column(type: 'integer')]
    private int $base_shield = 0;

    #[column(type: 'smallint')]
    private int $base_damage = 0;

    #[column(type: 'smallint')]
    private int $base_sensor_range = 0;

    #[Column(type: 'integer')]
    private int $base_warpdrive = 0;

    #[column(type: 'smallint')]
    private int $special_slots = 0;

    public function getEvadeChance(): int
    {
        return $this->evade_chance;
    }

    public function getHitChance(): int
    {
        return $this->hit_chance;
    }

    public function getModuleLevel(): int
    {
        return $this->module_level;
    }

    public function getBaseCrew(): int
    {
        return $this->base_crew;
    }

    public function getBaseEps(): int
    {
        return $this->base_eps;
    }

    public function getBaseReactor(): int
    {
        return $this->base_reactor;
    }

    public function getBaseHull(): int
    {
        return $this->base_hull;
    }

    public function getBaseShield(): int
    {
        return $this->base_shield;
    }

    public function getBaseDamage(): int
    {
        return $this->base_damage;
    }

    public function getBaseSensorRange(): int
    {
        return $this->base_sensor_range;
    }

    public function getBaseWarpDrive(): int
    {
        return $this->base_warpdrive;
    }

    public function getSpecialSlots(): int
    {
        return $this->special_slots;
    }
}
