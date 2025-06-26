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
use Stu\Orm\Repository\BuildingCommodityRepository;

#[Table(name: 'stu_buildings_commodity')]
#[Index(name: 'building_commodity_building_idx', columns: ['buildings_id'])]
#[Index(name: 'commodity_count_idx', columns: ['commodity_id', 'count'])]
#[Entity(repositoryClass: BuildingCommodityRepository::class)]
class BuildingCommodity
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $buildings_id = 0;

    #[Column(type: 'integer')]
    private int $commodity_id = 0;

    #[Column(type: 'integer')]
    private int $count = 0;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Commodity $commodity;

    /**
     * @var Building
     */
    #[ManyToOne(targetEntity: Building::class, inversedBy: 'buildingCommodities')]
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

    public function setBuildingId(int $buildingId): BuildingCommodity
    {
        $this->buildings_id = $buildingId;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function setCommodityId(int $commodityId): BuildingCommodity
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): BuildingCommodity
    {
        $this->count = $amount;

        return $this;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }
}
