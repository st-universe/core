<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepository;

#[Table(name: 'stu_rumps_buildingfunction')]
#[Index(name: 'building_function_ship_rump_idx', columns: ['rump_id'])]
#[Index(name: 'building_function_idx', columns: ['building_function'])]
#[Entity(repositoryClass: ShipRumpBuildingFunctionRepository::class)]
class ShipRumpBuildingFunction
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $rump_id = 0;

    #[Column(type: 'integer', enumType: BuildingFunctionEnum::class)]
    private BuildingFunctionEnum $building_function = BuildingFunctionEnum::BASE_CAMP;

    public function getId(): int
    {
        return $this->id;
    }

    public function setRumpId(int $rumpId): ShipRumpBuildingFunction
    {
        $this->rump_id = $rumpId;

        return $this;
    }

    public function getBuildingFunction(): BuildingFunctionEnum
    {
        return $this->building_function;
    }

    public function setBuildingFunction(BuildingFunctionEnum $buildingFunction): ShipRumpBuildingFunction
    {
        $this->building_function = $buildingFunction;

        return $this;
    }
}
