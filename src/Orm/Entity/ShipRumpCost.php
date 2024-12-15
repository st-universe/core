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
use Override;
use Stu\Orm\Repository\ShipRumpCostRepository;

#[Table(name: 'stu_rump_costs')]
#[Index(name: 'rump_cost_ship_rump_idx', columns: ['rump_id'])]
#[Entity(repositoryClass: ShipRumpCostRepository::class)]
class ShipRumpCost implements ShipRumpCostInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $rump_id = 0;

    #[Column(type: 'integer')]
    private int $commodity_id = 0;

    #[Column(type: 'integer')]
    private int $count = 0;

    #[ManyToOne(targetEntity: 'Commodity')]
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $commodity;

    #[ManyToOne(targetEntity: 'SpacecraftRump', inversedBy: 'buildingCosts')]
    #[JoinColumn(name: 'rump_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftRumpInterface $spacecraftRump;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    #[Override]
    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    #[Override]
    public function setCommodityId(int $commodityId): ShipRumpCostInterface
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    #[Override]
    public function getAmount(): int
    {
        return $this->count;
    }

    #[Override]
    public function setAmount(int $amount): ShipRumpCostInterface
    {
        $this->count = $amount;

        return $this;
    }

    #[Override]
    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }
}
