<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\BuildingCostRepository")
 * @Table(
 *     name="stu_buildings_cost",
 *     indexes={
 *          @Index(name="building_idx", columns={"buildings_id"})
 * })
 **/
class BuildingCost implements BuildingCostInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
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
    private $good;

    /**
     * @ManyToOne(targetEntity="Building", inversedBy="buildingCosts")
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

    public function setBuildingId(int $buildingId): BuildingCostInterface
    {
        $this->buildings_id = $buildingId;

        return $this;
    }

    public function getGoodId(): int
    {
        return $this->goods_id;
    }

    public function setGoodId(int $goodId): BuildingCostInterface
    {
        $this->goods_id = $goodId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): BuildingCostInterface
    {
        $this->count = $amount;

        return $this;
    }

    public function getGood(): CommodityInterface
    {
        return $this->good;
    }

    public function getHalfAmount(): int
    {
        return (int) floor($this->getAmount() / 2);
    }
}
