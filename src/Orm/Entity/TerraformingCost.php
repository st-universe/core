<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

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
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $terraforming_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $commodity_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $count = 0;

    /**
     * @var CommodityInterface
     *
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Commodity")
     * @JoinColumn(name="commodity_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $commodity;

    /**
     * @var TerraformingInterface
     *
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
