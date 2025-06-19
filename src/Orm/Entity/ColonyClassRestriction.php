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
use Override;
use Stu\Orm\Repository\ColonyClassRestrictionRepository;

#[Table(name: 'stu_colony_class_restriction')]
#[Entity(repositoryClass: ColonyClassRestrictionRepository::class)]
class ColonyClassRestriction implements ColonyClassRestrictionInterface
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
    #[JoinColumn(name: 'colony_class_id', referencedColumnName: 'id', nullable: false)]
    private ColonyClassInterface $colonyClass;

    #[ManyToOne(targetEntity: Terraforming::class)]
    #[JoinColumn(name: 'terraforming_id', referencedColumnName: 'id', nullable: true)]
    private ?TerraformingInterface $terraforming = null;

    #[ManyToOne(targetEntity: Building::class)]
    #[JoinColumn(name: 'building_id', referencedColumnName: 'id', nullable: true)]
    private ?BuildingInterface $building = null;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getColonyClass(): ColonyClassInterface
    {
        return $this->colonyClass;
    }

    #[Override]
    public function getTerraformingId(): ?int
    {
        return $this->terraforming_id;
    }

    #[Override]
    public function setTerraformingId(?int $terraformingId): ColonyClassRestrictionInterface
    {
        $this->terraforming_id = $terraformingId;
        return $this;
    }

    #[Override]
    public function getTerraforming(): ?TerraformingInterface
    {
        return $this->terraforming;
    }

    #[Override]
    public function getBuildingId(): ?int
    {
        return $this->building_id;
    }

    #[Override]
    public function setBuildingId(?int $buildingId): ColonyClassRestrictionInterface
    {
        $this->building_id = $buildingId;
        return $this;
    }

    #[Override]
    public function getBuilding(): ?BuildingInterface
    {
        return $this->building;
    }
}
