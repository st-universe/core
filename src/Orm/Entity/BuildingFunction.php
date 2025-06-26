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
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Repository\BuildingFunctionRepository;

#[Table(name: 'stu_buildings_functions')]
#[Index(name: 'building_function_building_idx', columns: ['buildings_id'])]
#[Index(name: 'building_function_function_idx', columns: ['function'])]
#[Entity(repositoryClass: BuildingFunctionRepository::class)]
class BuildingFunction
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $buildings_id = 0;

    #[Column(type: 'smallint', enumType: BuildingFunctionEnum::class)]
    private BuildingFunctionEnum $function = BuildingFunctionEnum::COLONY_CENTRAL;

    /**
     * @var Building
     */
    #[ManyToOne(targetEntity: Building::class, inversedBy: 'buildingFunctions')]
    #[JoinColumn(name: 'buildings_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $building;

    public function getId(): int
    {
        return $this->id;
    }

    public function getBuildingId(): int
    {
        return $this->buildings_id;
    }

    public function setBuildingId(int $buildingId): BuildingFunction
    {
        $this->buildings_id = $buildingId;

        return $this;
    }

    public function getFunction(): BuildingFunctionEnum
    {
        return $this->function;
    }

    public function setFunction(BuildingFunctionEnum $function): BuildingFunction
    {
        $this->function = $function;

        return $this;
    }
}
