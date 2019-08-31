<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity
 * @Entity(repositoryClass="Stu\Orm\Repository\BuildingGoodRepository")
 * @Table(
 *     name="stu_buildings_goods",
 *     indexes={
 *          @Index(name="building_idx", columns={"buildings_id"}),
 *          @Index(name="good_count_idx", columns={"goods_id","count"})
 * })
 **/
class BuildingGood implements BuildingGoodInterface
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
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Commodity")
     * @JoinColumn(name="goods_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $good;

    public function getId(): int
    {
        return $this->id;
    }

    public function getBuildingId(): int
    {
        return $this->buildings_id;
    }

    public function setBuildingId(int $buildingId): BuildingGoodInterface
    {
        $this->buildings_id = $buildingId;

        return $this;
    }

    public function getGoodId(): int
    {
        return $this->goods_id;
    }

    public function setGoodId(int $goodId): BuildingGoodInterface
    {
        $this->goods_id = $goodId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): BuildingGoodInterface
    {
        $this->count = $amount;

        return $this;
    }

    public function getGood(): CommodityInterface
    {
        return $this->good;
    }
}
