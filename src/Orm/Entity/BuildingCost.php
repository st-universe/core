<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\BuildingCostRepository")
 * @Table(
 *     name="stu_buildings_cost",
 *     indexes={
 *         @Index(name="building_cost_building_idx", columns={"buildings_id"})
 *     })
 **/
class BuildingCost implements BuildingCostInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="integer") *
     *
     */
    private int $buildings_id = 0;

    /**
     * @Column(type="integer") *
     *
     */
    private int $commodity_id = 0;

    /**
     * @Column(type="integer") *
     *
     */
    private int $count = 0;

    /**
     *
     * @ManyToOne(targetEntity="Commodity")
     * @JoinColumn(name="commodity_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private CommodityInterface $commodity;

    /**
     * @var BuildingInterface
     *
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

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function setCommodityId(int $commodityId): BuildingCostInterface
    {
        $this->commodity_id = $commodityId;

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

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    public function getHalfAmount(): int
    {
        return (int) ceil($this->getAmount() / 2);
    }
}
