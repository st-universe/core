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
use Stu\Orm\Repository\ShipRumpCostRepository;

#[Table(name: 'stu_rump_costs')]
#[Index(name: 'rump_cost_ship_rump_idx', columns: ['rump_id'])]
#[Entity(repositoryClass: ShipRumpCostRepository::class)]
class ShipRumpCost
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

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Commodity $commodity;

    #[ManyToOne(targetEntity: SpacecraftRump::class, inversedBy: 'buildingCosts')]
    #[JoinColumn(name: 'rump_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftRump $spacecraftRump;

    public function getId(): int
    {
        return $this->id;
    }

    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function setCommodityId(int $commodityId): ShipRumpCost
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): ShipRumpCost
    {
        $this->count = $amount;

        return $this;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }
}
