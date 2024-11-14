<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepository;

#[Table(name: 'stu_rumps_buildingfunction')]
#[Index(name: 'building_function_ship_rump_idx', columns: ['rump_id'])]
#[Index(name: 'building_function_idx', columns: ['building_function'])]
#[Entity(repositoryClass: ShipRumpBuildingFunctionRepository::class)]
class ShipRumpBuildingFunction implements ShipRumpBuildingFunctionInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $rump_id = 0;

    #[Column(type: 'integer', enumType: BuildingFunctionEnum::class)]
    private BuildingFunctionEnum $building_function = BuildingFunctionEnum::BUILDING_FUNCTION_BASE_CAMP;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getShipRumpId(): int
    {
        return $this->rump_id;
    }

    #[Override]
    public function setShipRumpId(int $shipRumpId): ShipRumpBuildingFunctionInterface
    {
        $this->rump_id = $shipRumpId;

        return $this;
    }

    #[Override]
    public function getBuildingFunction(): BuildingFunctionEnum
    {
        return $this->building_function;
    }

    #[Override]
    public function setBuildingFunction(BuildingFunctionEnum $buildingFunction): ShipRumpBuildingFunctionInterface
    {
        $this->building_function = $buildingFunction;

        return $this;
    }
}
