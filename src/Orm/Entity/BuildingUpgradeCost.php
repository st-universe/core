<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\BuildingUpgradeCostRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_buildings_upgrades_cost')]
#[Index(name: 'buildings_upgrades_idx', columns: ['buildings_upgrades_id'])]
#[Entity(repositoryClass: BuildingUpgradeCostRepository::class)]
class BuildingUpgradeCost implements BuildingUpgradeCostInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'bigint')]
    private int $buildings_upgrades_id;

    #[Column(type: 'integer')]
    private int $commodity_id;

    #[Column(type: 'integer')]
    private int $amount;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $commodity;

    #[ManyToOne(targetEntity: BuildingUpgrade::class)]
    #[JoinColumn(name: 'buildings_upgrades_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private BuildingUpgradeInterface $upgrade;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function setBuildingUpgradeId(int $building_upgrade_id): BuildingUpgradeCostInterface
    {
        $this->buildings_upgrades_id = $building_upgrade_id;

        return $this;
    }

    #[Override]
    public function getBuildingUpgradeId(): int
    {
        return $this->buildings_upgrades_id;
    }

    #[Override]
    public function setCommodityId(int $commodityId): BuildingUpgradeCostInterface
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    #[Override]
    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    #[Override]
    public function setAmount(int $amount): BuildingUpgradeCostInterface
    {
        $this->amount = $amount;

        return $this;
    }

    #[Override]
    public function getAmount(): int
    {
        return $this->amount;
    }

    #[Override]
    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    #[Override]
    public function getUpgrade(): BuildingUpgradeInterface
    {
        return $this->upgrade;
    }
}
