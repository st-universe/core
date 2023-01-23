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
 * @Entity(repositoryClass="Stu\Orm\Repository\BuildingUpgradeCostRepository")
 * @Table(
 *      name="stu_buildings_upgrades_cost",
 *      indexes={
 *          @Index(name="buildings_upgrades_idx", columns={"buildings_upgrades_id"})
 *      }
 * )
 **/
class BuildingUpgradeCost implements BuildingUpgradeCostInterface
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
     * @Column(type="bigint")
     *
     * @var int
     */
    private $buildings_upgrades_id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $commodity_id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $amount;

    /**
     * @var CommodityInterface
     *
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Commodity")
     * @JoinColumn(name="commodity_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $commodity;

    /**
     * @var BuildingUpgradeInterface
     *
     * @ManyToOne(targetEntity="Stu\Orm\Entity\BuildingUpgrade")
     * @JoinColumn(name="buildings_upgrades_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $upgrade;

    public function getId(): int
    {
        return $this->id;
    }

    public function setBuildingUpgradeId(int $building_upgrade_id): BuildingUpgradeCostInterface
    {
        $this->buildings_upgrades_id = $building_upgrade_id;

        return $this;
    }

    public function getBuildingUpgradeId(): int
    {
        return $this->buildings_upgrades_id;
    }

    public function setCommodityId(int $commodityId): BuildingUpgradeCostInterface
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function setAmount(int $amount): BuildingUpgradeCostInterface
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    public function getUpgrade(): BuildingUpgradeInterface
    {
        return $this->upgrade;
    }
}
