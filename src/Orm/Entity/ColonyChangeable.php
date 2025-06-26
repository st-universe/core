<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_colony_changeable')]
#[Entity]
class ColonyChangeable
{
    #[Id]
    #[OneToOne(targetEntity: Colony::class, inversedBy: 'changeable')]
    #[JoinColumn(name: 'colony_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Colony $colony;

    #[Column(type: 'integer', length: 5)]
    private int $bev_work = 0;

    #[Column(type: 'integer', length: 5)]
    private int $bev_free = 0;

    #[Column(type: 'integer', length: 5)]
    private int $bev_max = 0;

    #[Column(type: 'integer', length: 5)]
    private int $eps = 0;

    #[Column(type: 'integer', length: 5)]
    private int $max_eps = 0;

    #[Column(type: 'integer', length: 5)]
    private int $max_storage = 0;

    #[Column(type: 'integer', length: 5)]
    private int $populationlimit = 0;

    #[Column(type: 'boolean')]
    private bool $immigrationstate = true;

    #[Column(type: 'integer', length: 6, nullable: true)]
    private ?int $shields = 0;

    #[Column(type: 'integer', length: 6, nullable: true)]
    private ?int $shield_frequency = 0;

    #[ManyToOne(targetEntity: TorpedoType::class)]
    #[JoinColumn(name: 'torpedo_type', referencedColumnName: 'id')]
    private ?TorpedoType $torpedo = null;

    public function getColony(): Colony
    {
        return $this->colony;
    }

    public function getWorkers(): int
    {
        return $this->bev_work;
    }

    public function setWorkers(int $bev_work): ColonyChangeable
    {
        $this->bev_work = $bev_work;
        return $this;
    }

    public function getWorkless(): int
    {
        return $this->bev_free;
    }

    public function setWorkless(int $bev_free): ColonyChangeable
    {
        $this->bev_free = $bev_free;
        return $this;
    }

    public function getMaxBev(): int
    {
        return $this->bev_max;
    }

    public function setMaxBev(int $bev_max): ColonyChangeable
    {
        $this->bev_max = $bev_max;
        return $this;
    }

    public function getEps(): int
    {
        return $this->eps;
    }

    public function setEps(int $eps): ColonyChangeable
    {
        $this->eps = $eps;
        return $this;
    }

    public function getMaxEps(): int
    {
        return $this->max_eps;
    }

    public function setMaxEps(int $max_eps): ColonyChangeable
    {
        $this->max_eps = $max_eps;
        return $this;
    }

    public function getMaxStorage(): int
    {
        return $this->max_storage;
    }

    public function setMaxStorage(int $max_storage): ColonyChangeable
    {
        $this->max_storage = $max_storage;
        return $this;
    }

    public function getPopulationlimit(): int
    {
        return $this->populationlimit;
    }

    public function setPopulationlimit(int $populationlimit): ColonyChangeable
    {
        $this->populationlimit = $populationlimit;
        return $this;
    }

    public function getImmigrationstate(): bool
    {
        return $this->immigrationstate;
    }

    public function setImmigrationstate(bool $immigrationstate): ColonyChangeable
    {
        $this->immigrationstate = $immigrationstate;
        return $this;
    }

    public function getShields(): ?int
    {
        return $this->shields;
    }

    public function setShields(?int $shields): ColonyChangeable
    {
        $this->shields = $shields;
        return $this;
    }

    public function getShieldFrequency(): ?int
    {
        return $this->shield_frequency;
    }

    public function setShieldFrequency(?int $shieldFrequency): ColonyChangeable
    {
        $this->shield_frequency = $shieldFrequency;
        return $this;
    }

    public function getTorpedo(): ?TorpedoType
    {
        return $this->torpedo;
    }

    public function setTorpedo(?TorpedoType $torpedoType): ColonyChangeable
    {
        $this->torpedo = $torpedoType;
        return $this;
    }

    public function getPopulation(): int
    {
        return $this->getWorkers() + $this->getWorkless();
    }

    public function getFreeHousing(): int
    {
        return $this->getMaxBev() - $this->getPopulation();
    }

    public function lowerEps(int $value): void
    {
        $this->setEps($this->getEps() - $value);
    }

    public function upperEps(int $value): void
    {
        $this->setEps($this->getEps() + $value);
    }
}
