<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\BuildingUpgradeCostRepository")
 * @Table(
 *      name="stu_buildings_upgrades_cost",
 *      indexes={@Index(name="buildings_upgrades_idx", columns={"buildings_upgrades_id"})}
 * )
 **/
class BuildingUpgradeCost implements BuildingUpgradeCostInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="bigint") * */
    private $buildings_upgrades_id;

    /** @Column(type="integer") * */
    private $commodity_id;

    /** @Column(type="integer") * */
    private $amount;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\Commodity")
     * @JoinColumn(name="commodity_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $commodity;

    /**
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
