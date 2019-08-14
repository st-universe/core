<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Good;

/**
 * @Entity
 * @Table(name="stu_buildings_upgrades_cost",indexes={@Index(name="buildings_upgrades_idx", columns={"buildings_upgrades_id"})})
 * @Entity(repositoryClass="Stu\Orm\Repository\BuildingUpgradeCostRepository")
 **/
final class BuildingUpgradeCost implements BuildingUpgradeCostInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="bigint") * */
    private $buildings_upgrades_id;

    /** @Column(type="integer") * */
    private $good_id;

    /** @Column(type="integer") * */
    private $amount;

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

    public function setGoodId(int $good_id): BuildingUpgradeCostInterface
    {
        $this->good_id = $good_id;

        return $this;
    }

    public function getGoodId(): int {
        return $this->good_id;
    }

    public function setAmount(int $amount): BuildingUpgradeCostInterface
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmount(): int {
        return $this->amount;
    }

    public function getGood(): Good {
        return ResourceCache()->getGood($this->getGoodId());
    }
}
