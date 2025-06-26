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

#[Table(name: 'stu_torpedo_cost')]
#[Entity]
class TorpedoTypeCost
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $torpedo_type_id = 0;

    #[Column(type: 'integer')]
    private int $commodity_id = 0;

    #[Column(type: 'integer')]
    private int $count = 0;

    #[ManyToOne(targetEntity: TorpedoType::class)]
    #[JoinColumn(name: 'torpedo_type_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private TorpedoType $torpedoType;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Commodity $commodity;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTorpedoType(): TorpedoType
    {
        return $this->torpedoType;
    }

    public function getTorpedoTypeId(): int
    {
        return $this->torpedo_type_id;
    }

    public function setTorpedoTypeId(int $torpedoTypeId): TorpedoTypeCost
    {
        $this->torpedo_type_id = $torpedoTypeId;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function setCommodityId(int $commodityId): TorpedoTypeCost
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): TorpedoTypeCost
    {
        $this->count = $amount;

        return $this;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }
}
