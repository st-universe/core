<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\TerraformingCostRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_terraforming_cost')]
#[Index(name: 'terraforming_idx', columns: ['terraforming_id'])]
#[Entity(repositoryClass: TerraformingCostRepository::class)]
class TerraformingCost implements TerraformingCostInterface
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
    #[JoinColumn(name: 'commodity_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $commodity;

    #[ManyToOne(targetEntity: Terraforming::class)]
    #[JoinColumn(name: 'terraforming_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private TerraformingInterface $terraforming;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getTerraformingId(): int
    {
        return $this->terraforming_id;
    }

    #[Override]
    public function setTerraformingId(int $terraformingId): TerraformingCostInterface
    {
        $this->terraforming_id = $terraformingId;

        return $this;
    }

    #[Override]
    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    #[Override]
    public function setCommodityId(int $commodityId): TerraformingCostInterface
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
    public function setAmount(int $amount): TerraformingCostInterface
    {
        $this->count = $amount;

        return $this;
    }

    #[Override]
    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }

    #[Override]
    public function getTerraforming(): TerraformingInterface
    {
        return $this->terraforming;
    }
}
