<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ColonyStorageRepository")
 * @Table(
 *     name="stu_colonies_storage",
 *     indexes={
 *         @Index(name="colony_id", columns={"colonies_id"})
 *     },
 *     uniqueConstraints={@UniqueConstraint(name="colony_commodity_cns", columns={"colonies_id", "goods_id"})}
 * )
 **/
class ColonyStorage implements ColonyStorageInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $colonies_id = 0;

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
     * @ManyToOne(targetEntity="Colony")
     * @JoinColumn(name="colonies_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $colony;

    public function getId(): int
    {
        return $this->id;
    }

    public function getColony(): ColonyInterface
    {
        return $this->colony;
    }

    public function setColony(ColonyInterface $colony): ColonyStorageInterface
    {
        $this->colony = $colony;
        return $this;
    }

    public function getGoodId(): int
    {
        return $this->goods_id;
    }

    public function setGoodId(int $commodityId): ColonyStorageInterface
    {
        $this->goods_id = $commodityId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): ColonyStorageInterface
    {
        $this->count = $amount;

        return $this;
    }

    public function getGood(): CommodityInterface
    {
        return $this->commodity;
    }

    public function setGood(CommodityInterface $commodity): ColonyStorageInterface
    {
        $this->commodity = $commodity;

        return $this;
    }
}
