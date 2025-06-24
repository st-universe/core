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
use Override;

#[Table(name: 'stu_colony_changeable')]
#[Entity]
class ColonyChangeable implements ColonyChangeableInterface
{
    #[Id]
    #[OneToOne(targetEntity: 'Colony', inversedBy: 'changeable')]
    #[JoinColumn(name: 'colony_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ColonyInterface $colony;

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

    #[ManyToOne(targetEntity: 'TorpedoType')]
    #[JoinColumn(name: 'torpedo_type', referencedColumnName: 'id')]
    private ?TorpedoTypeInterface $torpedo = null;

    #[Override]
    public function getColony(): ColonyInterface
    {
        return $this->colony;
    }

    #[Override]
    public function getWorkers(): int
    {
        return $this->bev_work;
    }

    #[Override]
    public function setWorkers(int $bev_work): ColonyChangeableInterface
    {
        $this->bev_work = $bev_work;
        return $this;
    }

    #[Override]
    public function getWorkless(): int
    {
        return $this->bev_free;
    }

    #[Override]
    public function setWorkless(int $bev_free): ColonyChangeableInterface
    {
        $this->bev_free = $bev_free;
        return $this;
    }

    #[Override]
    public function getMaxBev(): int
    {
        return $this->bev_max;
    }

    #[Override]
    public function setMaxBev(int $bev_max): ColonyChangeableInterface
    {
        $this->bev_max = $bev_max;
        return $this;
    }

    #[Override]
    public function getEps(): int
    {
        return $this->eps;
    }

    #[Override]
    public function setEps(int $eps): ColonyChangeableInterface
    {
        $this->eps = $eps;
        return $this;
    }

    #[Override]
    public function getMaxEps(): int
    {
        return $this->max_eps;
    }

    #[Override]
    public function setMaxEps(int $max_eps): ColonyChangeableInterface
    {
        $this->max_eps = $max_eps;
        return $this;
    }

    #[Override]
    public function getMaxStorage(): int
    {
        return $this->max_storage;
    }

    #[Override]
    public function setMaxStorage(int $max_storage): ColonyChangeableInterface
    {
        $this->max_storage = $max_storage;
        return $this;
    }

    #[Override]
    public function getPopulationlimit(): int
    {
        return $this->populationlimit;
    }

    #[Override]
    public function setPopulationlimit(int $populationlimit): ColonyChangeableInterface
    {
        $this->populationlimit = $populationlimit;
        return $this;
    }

    #[Override]
    public function getImmigrationstate(): bool
    {
        return $this->immigrationstate;
    }

    #[Override]
    public function setImmigrationstate(bool $immigrationstate): ColonyChangeableInterface
    {
        $this->immigrationstate = $immigrationstate;
        return $this;
    }

    #[Override]
    public function getShields(): ?int
    {
        return $this->shields;
    }

    #[Override]
    public function setShields(?int $shields): ColonyChangeableInterface
    {
        $this->shields = $shields;
        return $this;
    }

    #[Override]
    public function getShieldFrequency(): ?int
    {
        return $this->shield_frequency;
    }

    #[Override]
    public function setShieldFrequency(?int $shieldFrequency): ColonyChangeableInterface
    {
        $this->shield_frequency = $shieldFrequency;
        return $this;
    }

    #[Override]
    public function getTorpedo(): ?TorpedoTypeInterface
    {
        return $this->torpedo;
    }

    #[Override]
    public function setTorpedo(?TorpedoTypeInterface $torpedoType): ColonyChangeableInterface
    {
        $this->torpedo = $torpedoType;
        return $this;
    }

    #[Override]
    public function getPopulation(): int
    {
        return $this->getWorkers() + $this->getWorkless();
    }

    #[Override]
    public function getFreeHousing(): int
    {
        return $this->getMaxBev() - $this->getPopulation();
    }

    #[Override]
    public function lowerEps(int $value): void
    {
        $this->setEps($this->getEps() - $value);
    }

    #[Override]
    public function upperEps(int $value): void
    {
        $this->setEps($this->getEps() + $value);
    }
}
