<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_rumps_buildingfunction')]
#[Index(name: 'building_function_ship_rump_idx', columns: ['rump_id'])]
#[Index(name: 'building_function_idx', columns: ['building_function'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\ShipRumpBuildingFunctionRepository')]
class ShipRumpBuildingFunction implements ShipRumpBuildingFunctionInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $rump_id = 0;

    #[Column(type: 'integer')]
    private int $building_function = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getShipRumpId(): int
    {
        return $this->rump_id;
    }

    public function setShipRumpId(int $shipRumpId): ShipRumpBuildingFunctionInterface
    {
        $this->rump_id = $shipRumpId;

        return $this;
    }

    public function getBuildingFunction(): int
    {
        return $this->building_function;
    }

    public function setBuildingFunction(int $buildingFunction): ShipRumpBuildingFunctionInterface
    {
        $this->building_function = $buildingFunction;

        return $this;
    }
}
