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
use Stu\Orm\Repository\TerraformingCostRepository;

#[Table(name: 'stu_terraforming_cost')]
#[Index(name: 'terraforming_idx', columns: ['terraforming_id'])]
#[Entity(repositoryClass: TerraformingCostRepository::class)]
class TerraformingCost
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $terraforming_id = 0;

    #[Column(type: 'integer')]
    private int $commodity_id = 0;

    #[Column(type: 'integer')]
    private int $count = 0;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Commodity $commodity;

    #[ManyToOne(targetEntity: Terraforming::class)]
    #[JoinColumn(name: 'terraforming_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Terraforming $terraforming;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTerraformingId(): int
    {
        return $this->terraforming_id;
    }

    public function setTerraformingId(int $terraformingId): TerraformingCost
    {
        $this->terraforming_id = $terraformingId;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function setCommodityId(int $commodityId): TerraformingCost
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): TerraformingCost
    {
        $this->count = $amount;

        return $this;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }

    public function getTerraforming(): Terraforming
    {
        return $this->terraforming;
    }
}
