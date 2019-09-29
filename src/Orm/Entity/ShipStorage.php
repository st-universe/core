<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipStorageRepository")
 * @Table(
 *     name="stu_ships_storage",
 *     indexes={
 *         @Index(name="ship_idx", columns={"ships_id"})
 *     },
 *     uniqueConstraints={@UniqueConstraint(name="ship_commodity_cns", columns={"ships_id", "goods_id"})}
 * )
 **/
class ShipStorage implements ShipStorageInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $ships_id = 0;

    /** @Column(type="integer") */
    private $goods_id = 0;

    /** @Column(type="integer") */
    private $count = 0;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Commodity")
     * @JoinColumn(name="goods_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $commodity;

    /**
     * @ManyToOne(targetEntity="Ship", inversedBy="storage")
     * @JoinColumn(name="ships_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ship;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCommodityId(): int
    {
        return $this->goods_id;
    }

    public function setCommodityId(int $commodityId): ShipStorageInterface
    {
        $this->goods_id = $commodityId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): ShipStorageInterface
    {
        $this->count = $amount;

        return $this;
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    public function setCommodity(CommodityInterface $commodity): ShipStorageInterface
    {
        $this->commodity = $commodity;

        return $this;
    }

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    public function setShip(ShipInterface $ship): ShipStorageInterface
    {
        $this->ship = $ship;
        return $this;
    }
}
