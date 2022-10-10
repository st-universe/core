<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TerraformingCostRepository")
 * @Table(
 *     name="stu_terraforming_cost",
 *     indexes={
 *          @Index(name="terraforming_idx", columns={"terraforming_id"})
 * })
 **/
class TerraformingCost implements TerraformingCostInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $terraforming_id = 0;

    /** @Column(type="integer") * */
    private $commodity_id = 0;

    /** @Column(type="integer") * */
    private $count = 0;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Commodity")
     * @JoinColumn(name="commodity_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $commodity;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Terraforming")
     * @JoinColumn(name="terraforming_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $terraforming;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTerraformingId(): int
    {
        return $this->terraforming_id;
    }

    public function setTerraformingId(int $terraformingId): TerraformingCostInterface
    {
        $this->terraforming_id = $terraformingId;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function setCommodityId(int $commodityId): TerraformingCostInterface
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): TerraformingCostInterface
    {
        $this->count = $amount;

        return $this;
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    public function getTerraforming(): TerraformingInterface
    {
        return $this->terraforming;
    }
}
