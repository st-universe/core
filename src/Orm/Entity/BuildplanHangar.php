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
use Override;
use Stu\Orm\Repository\BuildplanHangarRepository;

#[Table(name: 'stu_buildplans_hangar')]
#[UniqueConstraint(name: 'rump_idx', columns: ['rump_id'])]
#[Entity(repositoryClass: BuildplanHangarRepository::class)]
class BuildplanHangar implements BuildplanHangarInterface
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

    #[ManyToOne(targetEntity: 'TorpedoType')]
    #[JoinColumn(name: 'default_torpedo_type_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?TorpedoTypeInterface $defaultTorpedoType = null;

    #[ManyToOne(targetEntity: 'SpacecraftBuildplan')]
    #[JoinColumn(name: 'buildplan_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftBuildplanInterface $buildplan;

    #[ManyToOne(targetEntity: 'SpacecraftRump', inversedBy: 'startHangar')]
    #[JoinColumn(name: 'rump_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftRumpInterface $spacecraftRump;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getBuildplanId(): int
    {
        return $this->buildplan_id;
    }

    #[Override]
    public function setBuildplanId(int $buildplanId): BuildplanHangarInterface
    {
        $this->buildplan_id = $buildplanId;

        return $this;
    }

    #[Override]
    public function getDefaultTorpedoTypeId(): int
    {
        return $this->default_torpedo_type_id;
    }

    #[Override]
    public function setDefaultTorpedoTypeId(int $defaultTorpedoTypeId): BuildplanHangarInterface
    {
        $this->default_torpedo_type_id = $defaultTorpedoTypeId;

        return $this;
    }

    #[Override]
    public function getDefaultTorpedoType(): ?TorpedoTypeInterface
    {
        return $this->defaultTorpedoType;
    }

    #[Override]
    public function getBuildplan(): SpacecraftBuildplanInterface
    {
        return $this->buildplan;
    }

    #[Override]
    public function setStartEnergyCosts(int $startEnergyCosts): BuildplanHangarInterface
    {
        $this->start_energy_costs = $startEnergyCosts;
        return $this;
    }

    #[Override]
    public function getStartEnergyCosts(): int
    {
        return $this->start_energy_costs;
    }

    #[Override]
    public function getRumpId(): int
    {
        return $this->rump_id;
    }
}
