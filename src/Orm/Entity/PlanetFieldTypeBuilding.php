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
use Stu\Orm\Repository\PlanetFieldTypeBuildingRepository;

#[Table(name: 'stu_field_build')]
#[Index(name: 'type_building_idx', columns: ['type', 'buildings_id'])]
#[Index(name: 'type_building_research_idx', columns: ['type', 'research_id'])]
#[Entity(repositoryClass: PlanetFieldTypeBuildingRepository::class)]
class PlanetFieldTypeBuilding
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $type = 0;

    #[Column(type: 'integer')]
    private int $buildings_id = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $research_id = null;

    #[Column(type: 'boolean')]
    private bool $view = true;

    /**
     * @var Building
     */
    #[ManyToOne(targetEntity: Building::class, inversedBy: 'buildingPossibleFieldTypes')]
    #[JoinColumn(name: 'buildings_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $building;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFieldTypeId(): int
    {
        return $this->type;
    }

    public function setFieldTypeId(int $fieldTypeId): PlanetFieldTypeBuilding
    {
        $this->type = $fieldTypeId;

        return $this;
    }

    public function getBuildingId(): int
    {
        return $this->buildings_id;
    }

    public function setBuildingId(int $buildingId): PlanetFieldTypeBuilding
    {
        $this->buildings_id = $buildingId;

        return $this;
    }

    public function getResearchId(): ?int
    {
        return $this->research_id;
    }

    public function setResearchId(?int $researchId): PlanetFieldTypeBuilding
    {
        $this->research_id = $researchId;

        return $this;
    }

    public function getView(): bool
    {
        return $this->view;
    }

    public function setView(bool $view): PlanetFieldTypeBuilding
    {
        $this->view = $view;

        return $this;
    }
}
