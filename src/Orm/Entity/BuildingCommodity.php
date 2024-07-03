<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_buildings_commodity')]
#[Index(name: 'building_commodity_building_idx', columns: ['buildings_id'])]
#[Index(name: 'commodity_count_idx', columns: ['commodity_id', 'count'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\BuildingCommodityRepository')]
class BuildingCommodity implements BuildingCommodityInterface
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

    #[ManyToOne(targetEntity: 'Commodity')]
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $commodity;

    /**
     * @var BuildingInterface
     */
    #[ManyToOne(targetEntity: 'Building', inversedBy: 'buildingCommodities')]
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
    public function setBuildingId(int $buildingId): BuildingCommodityInterface
    {
        $this->buildings_id = $buildingId;

        return $this;
    }

    #[Override]
    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    #[Override]
    public function setCommodityId(int $commodityId): BuildingCommodityInterface
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    #[Override]
    public function getAmount(): int
    {
        return $this->count;
    }

    #[Override]
    public function setAmount(int $amount): BuildingCommodityInterface
    {
        $this->count = $amount;

        return $this;
    }

    #[Override]
    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }
}
