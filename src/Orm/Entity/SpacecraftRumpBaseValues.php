<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Override;

#[Table(name: 'stu_rump_base_values')]
#[Entity]
class SpacecraftRumpBaseValues implements SpacecraftRumpBaseValuesInterface
{
    #[Id]
    #[OneToOne(targetEntity: SpacecraftRump::class, inversedBy: 'baseValues')]
    #[JoinColumn(name: 'rump_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftRumpInterface $rump;

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

    #[Override]
    public function getEvadeChance(): int
    {
        return $this->evade_chance;
    }

    #[Override]
    public function getHitChance(): int
    {
        return $this->hit_chance;
    }

    #[Override]
    public function getModuleLevel(): int
    {
        return $this->module_level;
    }

    #[Override]
    public function getBaseCrew(): int
    {
        return $this->base_crew;
    }

    #[Override]
    public function getBaseEps(): int
    {
        return $this->base_eps;
    }

    #[Override]
    public function getBaseReactor(): int
    {
        return $this->base_reactor;
    }

    #[Override]
    public function getBaseHull(): int
    {
        return $this->base_hull;
    }

    #[Override]
    public function getBaseShield(): int
    {
        return $this->base_shield;
    }

    #[Override]
    public function getBaseDamage(): int
    {
        return $this->base_damage;
    }

    #[Override]
    public function getBaseSensorRange(): int
    {
        return $this->base_sensor_range;
    }

    #[Override]
    public function getBaseWarpDrive(): int
    {
        return $this->base_warpdrive;
    }

    #[Override]
    public function getSpecialSlots(): int
    {
        return $this->special_slots;
    }
}
