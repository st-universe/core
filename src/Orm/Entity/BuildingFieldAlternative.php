<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\BuildingFieldAlternativeRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_buildings_field_alternative')]
#[Index(name: 'building_field_idx', columns: ['fieldtype', 'buildings_id'])]
#[Entity(repositoryClass: BuildingFieldAlternativeRepository::class)]
class BuildingFieldAlternative implements BuildingFieldAlternativeInterface
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

    #[ManyToOne(targetEntity: 'Building')]
    #[JoinColumn(name: 'buildings_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private BuildingInterface $building;

    #[ManyToOne(targetEntity: 'Building')]
    #[JoinColumn(name: 'alternate_buildings_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private BuildingInterface $alternateBuilding;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getFieldType(): int
    {
        return $this->fieldtype;
    }

    #[Override]
    public function setFieldType(int $fieldType): BuildingFieldAlternativeInterface
    {
        $this->fieldtype = $fieldType;

        return $this;
    }

    #[Override]
    public function getBuildingId(): int
    {
        return $this->buildings_id;
    }

    #[Override]
    public function setBuildingId(int $buildingId): BuildingFieldAlternativeInterface
    {
        $this->buildings_id = $buildingId;

        return $this;
    }

    #[Override]
    public function getAlternativeBuildingId(): int
    {
        return $this->alternate_buildings_id;
    }

    #[Override]
    public function setAlternativeBuildingId(int $alternativeBuildingId): BuildingFieldAlternativeInterface
    {
        $this->alternate_buildings_id = $alternativeBuildingId;

        return $this;
    }

    #[Override]
    public function getAlternativeBuilding(): BuildingInterface
    {
        return $this->alternateBuilding;
    }

    #[Override]
    public function getResearchId(): ?int
    {
        return $this->research_id;
    }

    #[Override]
    public function setResearchId(?int $researchId): BuildingFieldAlternativeInterface
    {
        $this->research_id = $researchId;
        return $this;
    }

    #[Override]
    public function getBuilding(): BuildingInterface
    {
        return $this->building;
    }
}
