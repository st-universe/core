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
use Stu\Orm\Repository\ColonyClassRestrictionRepository;

#[Table(name: 'stu_colony_class_restriction')]
#[Entity(repositoryClass: ColonyClassRestrictionRepository::class)]
class ColonyClassRestriction
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer', nullable: true)]
    private ?int $terraforming_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $building_id = null;

    #[ManyToOne(targetEntity: ColonyClass::class)]
    #[JoinColumn(name: 'colony_class_id', nullable: false, referencedColumnName: 'id')]
    private ColonyClass $colonyClass;

    #[ManyToOne(targetEntity: Terraforming::class)]
    #[JoinColumn(name: 'terraforming_id', referencedColumnName: 'id', nullable: true)]
    private ?Terraforming $terraforming = null;

    #[ManyToOne(targetEntity: Building::class)]
    #[JoinColumn(name: 'building_id', referencedColumnName: 'id', nullable: true)]
    private ?Building $building = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getColonyClass(): ColonyClass
    {
        return $this->colonyClass;
    }

    public function getTerraformingId(): ?int
    {
        return $this->terraforming_id;
    }

    public function setTerraformingId(?int $terraformingId): ColonyClassRestriction
    {
        $this->terraforming_id = $terraformingId;
        return $this;
    }

    public function getTerraforming(): ?Terraforming
    {
        return $this->terraforming;
    }

    public function getBuildingId(): ?int
    {
        return $this->building_id;
    }

    public function setBuildingId(?int $buildingId): ColonyClassRestriction
    {
        $this->building_id = $buildingId;
        return $this;
    }

    public function getBuilding(): ?Building
    {
        return $this->building;
    }
}
