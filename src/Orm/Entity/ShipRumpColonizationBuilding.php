<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\ShipRumpColonizationBuildingRepository;

#[Table(name: 'stu_rumps_colonize_building')]
#[Index(name: 'rump_colonize_building_ship_rump_idx', columns: ['rump_id'])]
#[Entity(repositoryClass: ShipRumpColonizationBuildingRepository::class)]
class ShipRumpColonizationBuilding
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $rump_id = 0;

    #[Column(type: 'integer')]
    private int $building_id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    public function getBuildingId(): int
    {
        return $this->building_id;
    }

    public function setBuildingId(int $buildingId): ShipRumpColonizationBuilding
    {
        $this->building_id = $buildingId;

        return $this;
    }
}
