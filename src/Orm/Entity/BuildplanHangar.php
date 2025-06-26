<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Stu\Orm\Repository\BuildplanHangarRepository;

#[Table(name: 'stu_buildplans_hangar')]
#[UniqueConstraint(name: 'rump_idx', columns: ['rump_id'])]
#[Entity(repositoryClass: BuildplanHangarRepository::class)]
class BuildplanHangar
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $rump_id = 0;

    #[Column(type: 'integer')]
    private int $buildplan_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $default_torpedo_type_id = null;

    #[Column(type: 'integer')]
    private int $start_energy_costs;

    #[ManyToOne(targetEntity: TorpedoType::class)]
    #[JoinColumn(name: 'default_torpedo_type_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?TorpedoType $defaultTorpedoType = null;

    #[ManyToOne(targetEntity: SpacecraftBuildplan::class)]
    #[JoinColumn(name: 'buildplan_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftBuildplan $buildplan;

    #[ManyToOne(targetEntity: SpacecraftRump::class, inversedBy: 'startHangar')]
    #[JoinColumn(name: 'rump_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftRump $spacecraftRump;

    public function getId(): int
    {
        return $this->id;
    }

    public function getBuildplanId(): int
    {
        return $this->buildplan_id;
    }

    public function setBuildplanId(int $buildplanId): BuildplanHangar
    {
        $this->buildplan_id = $buildplanId;

        return $this;
    }

    public function getDefaultTorpedoTypeId(): int
    {
        return $this->default_torpedo_type_id;
    }

    public function setDefaultTorpedoTypeId(int $defaultTorpedoTypeId): BuildplanHangar
    {
        $this->default_torpedo_type_id = $defaultTorpedoTypeId;

        return $this;
    }

    public function getDefaultTorpedoType(): ?TorpedoType
    {
        return $this->defaultTorpedoType;
    }

    public function getBuildplan(): SpacecraftBuildplan
    {
        return $this->buildplan;
    }

    public function setStartEnergyCosts(int $startEnergyCosts): BuildplanHangar
    {
        $this->start_energy_costs = $startEnergyCosts;
        return $this;
    }

    public function getStartEnergyCosts(): int
    {
        return $this->start_energy_costs;
    }

    public function getRumpId(): int
    {
        return $this->rump_id;
    }
}
