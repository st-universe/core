<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ColonyClassDepositRepository")
 * @Table(
 *     name="stu_colony_class_deposit"
 * )
 **/
class ColonyClassDeposit implements ColonyClassDepositInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     */
    private $colony_class_id;

    /** 
     * @Id
     * @Column(type="integer")
     */
    private $commodity_id;

    /** @Column(type="integer") */
    private $min_amount = 0;

    /** @Column(type="integer") */
    private $max_amount = 0;

    /**
     * @ManyToOne(targetEntity="ColonyClass")
     * @JoinColumn(name="colony_class_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $colonyClass;

    /**
     * @ManyToOne(targetEntity="Commodity")
     * @JoinColumn(name="commodity_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $commodity;

    public function getColonyClass(): ColonyClassInterface
    {
        return $this->colonyClass;
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    public function getMinAmount(): int
    {
        return $this->min_amount;
    }

    public function getMaxAmount(): int
    {
        return $this->max_amount;
    }
}
