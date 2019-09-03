<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipRumpCostRepository")
 * @Table(
 *     name="stu_rump_costs",
 *     indexes={
 *         @Index(name="ship_rump_idx", columns={"rump_id"})
 *     }
 * )
 **/
class ShipRumpCost implements ShipRumpCostInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $rump_id = 0;

    /** @Column(type="integer") * */
    private $good_id = 0;

    /** @Column(type="integer") * */
    private $count = 0;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Commodity")
     * @JoinColumn(name="good_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $commodity;

    public function getId(): int
    {
        return $this->id;
    }

    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    public function setRumpId(int $shipRumpId): ShipRumpCostInterface
    {
        $this->rump_id = $shipRumpId;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->good_id;
    }

    public function setCommodityId(int $commodityId): ShipRumpCostInterface
    {
        $this->good_id = $commodityId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): ShipRumpCostInterface
    {
        $this->count = $amount;

        return $this;
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }
}
