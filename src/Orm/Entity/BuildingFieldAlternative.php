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
use Stu\Orm\Repository\BuildingFieldAlternativeRepository;

#[Table(name: 'stu_buildings_field_alternative')]
#[Index(name: 'building_field_idx', columns: ['fieldtype', 'buildings_id'])]
#[Entity(repositoryClass: BuildingFieldAlternativeRepository::class)]
class BuildingFieldAlternative
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $fieldtype = 0;

    #[Column(type: 'integer')]
    private int $buildings_id = 0;

    #[Column(type: 'integer')]
    private int $alternate_buildings_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $research_id = null;

    #[ManyToOne(targetEntity: Building::class)]
    #[JoinColumn(name: 'buildings_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Building $building;

    #[ManyToOne(targetEntity: Building::class)]
    #[JoinColumn(name: 'alternate_buildings_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Building $alternateBuilding;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFieldType(): int
    {
        return $this->fieldtype;
    }

    public function setFieldType(int $fieldType): BuildingFieldAlternative
    {
        $this->fieldtype = $fieldType;

        return $this;
    }

    public function getBuildingId(): int
    {
        return $this->buildings_id;
    }

    public function setBuildingId(int $buildingId): BuildingFieldAlternative
    {
        $this->buildings_id = $buildingId;

        return $this;
    }

    public function getAlternativeBuildingId(): int
    {
        return $this->alternate_buildings_id;
    }

    public function setAlternativeBuildingId(int $alternativeBuildingId): BuildingFieldAlternative
    {
        $this->alternate_buildings_id = $alternativeBuildingId;

        return $this;
    }

    public function getAlternativeBuilding(): Building
    {
        return $this->alternateBuilding;
    }

    public function getResearchId(): ?int
    {
        return $this->research_id;
    }

    public function setResearchId(?int $researchId): BuildingFieldAlternative
    {
        $this->research_id = $researchId;
        return $this;
    }

    public function getBuilding(): Building
    {
        return $this->building;
    }
}
