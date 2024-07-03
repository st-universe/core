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
use Override;
use Stu\Orm\Repository\BuildingFunctionRepository;

#[Table(name: 'stu_buildings_functions')]
#[Index(name: 'building_function_building_idx', columns: ['buildings_id'])]
#[Index(name: 'building_function_function_idx', columns: ['function'])]
#[Entity(repositoryClass: BuildingFunctionRepository::class)]
class BuildingFunction implements BuildingFunctionInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $buildings_id = 0;

    #[Column(type: 'smallint')]
    private int $function = 0;

    /**
     * @var BuildingInterface
     */
    #[ManyToOne(targetEntity: 'Building', inversedBy: 'buildingFunctions')]
    #[JoinColumn(name: 'buildings_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $building;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getBuildingId(): int
    {
        return $this->buildings_id;
    }

    #[Override]
    public function setBuildingId(int $buildingId): BuildingFunctionInterface
    {
        $this->buildings_id = $buildingId;

        return $this;
    }

    #[Override]
    public function getFunction(): int
    {
        return $this->function;
    }

    #[Override]
    public function setFunction(int $function): BuildingFunctionInterface
    {
        $this->function = $function;

        return $this;
    }
}
