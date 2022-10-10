<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\BuildingCommodityRepository")
 * @Table(
 *     name="stu_buildings_goods",
 *     indexes={
 *          @Index(name="building_good_building_idx", columns={"buildings_id"}),
 *          @Index(name="good_count_idx", columns={"goods_id","count"})
 * })
 **/
class BuildingCommodity implements BuildingCommodityInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $buildings_id = 0;

    /** @Column(type="integer") * */
    private $goods_id = 0;

    /** @Column(type="integer") * */
    private $count = 0;

    /**
     * @ManyToOne(targetEntity="Commodity")
     * @JoinColumn(name="goods_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $commodity;

    /**
     * @ManyToOne(targetEntity="Building", inversedBy="buildingCommodities")
     * @JoinColumn(name="buildings_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $building;

    public function getId(): int
    {
        return $this->id;
    }

    public function getBuildingId(): int
    {
        return $this->buildings_id;
    }

    public function setBuildingId(int $buildingId): BuildingCommodityInterface
    {
        $this->buildings_id = $buildingId;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->goods_id;
    }

    public function setCommodityId(int $commodityId): BuildingCommodityInterface
    {
        $this->goods_id = $commodityId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): BuildingCommodityInterface
    {
        $this->count = $amount;

        return $this;
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }
}
