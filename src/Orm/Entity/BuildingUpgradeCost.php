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
use Stu\Orm\Repository\BuildingUpgradeCostRepository;

#[Table(name: 'stu_buildings_upgrades_cost')]
#[Index(name: 'buildings_upgrades_idx', columns: ['buildings_upgrades_id'])]
#[Entity(repositoryClass: BuildingUpgradeCostRepository::class)]
class BuildingUpgradeCost
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
    #[JoinColumn(name: 'commodity_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Commodity $commodity;

    #[ManyToOne(targetEntity: BuildingUpgrade::class)]
    #[JoinColumn(name: 'buildings_upgrades_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private BuildingUpgrade $upgrade;

    public function getId(): int
    {
        return $this->id;
    }

    public function setBuildingUpgradeId(int $building_upgrade_id): BuildingUpgradeCost
    {
        $this->buildings_upgrades_id = $building_upgrade_id;

        return $this;
    }

    public function getBuildingUpgradeId(): int
    {
        return $this->buildings_upgrades_id;
    }

    public function setCommodityId(int $commodityId): BuildingUpgradeCost
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function setAmount(int $amount): BuildingUpgradeCost
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }

    public function getUpgrade(): BuildingUpgrade
    {
        return $this->upgrade;
    }
}
