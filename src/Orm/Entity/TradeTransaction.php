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
use Stu\Orm\Repository\TradeTransactionRepository;

#[Table(name: 'stu_trade_transaction')]
#[Index(name: 'trade_transaction_date_tradepost_idx', columns: ['date', 'tradepost_id'])]
#[Entity(repositoryClass: TradeTransactionRepository::class)]
class TradeTransaction implements TradeTransactionInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $wg_id = 0;

    #[Column(type: 'integer')]
    private int $wg_count = 0;

    #[Column(type: 'integer')]
    private int $gg_id = 0;

    #[Column(type: 'integer')]
    private int $gg_count = 0;

    #[Column(type: 'integer')]
    private int $date = 0;

    #[Column(type: 'integer', nullable: true)]
    private ?int $tradepost_id = 0;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'wg_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $wantedCommodity;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'gg_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private CommodityInterface $offeredCommodity;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getWantedCommodityId(): int
    {
        return $this->wg_id;
    }

    #[Override]
    public function setWantedCommodityId(int $wantedCommodityId): TradeTransactionInterface
    {
        $this->wg_id = $wantedCommodityId;

        return $this;
    }

    #[Override]
    public function getWantedCommodityCount(): int
    {
        return $this->wg_count;
    }

    #[Override]
    public function setWantedCommodityCount(int $wantedCommodityCount): TradeTransactionInterface
    {
        $this->wg_count = $wantedCommodityCount;

        return $this;
    }

    #[Override]
    public function getOfferedCommodityId(): int
    {
        return $this->gg_id;
    }

    #[Override]
    public function setOfferedCommodityId(int $offeredCommodityId): TradeTransactionInterface
    {
        $this->gg_id = $offeredCommodityId;

        return $this;
    }

    #[Override]
    public function getOfferedCommodityCount(): int
    {
        return $this->gg_count;
    }

    #[Override]
    public function setOfferedCommodityCount(int $offeredCommodityCount): TradeTransactionInterface
    {
        $this->gg_count = $offeredCommodityCount;

        return $this;
    }

    #[Override]
    public function getDate(): int
    {
        return $this->date;
    }

    #[Override]
    public function setDate(int $date): TradeTransactionInterface
    {
        $this->date = $date;

        return $this;
    }

    #[Override]
    public function getTradePostId(): int
    {
        return $this->tradepost_id;
    }

    #[Override]
    public function setTradePostId(int $tradepost_id): TradeTransactionInterface
    {
        $this->tradepost_id = $tradepost_id;

        return $this;
    }

    #[Override]
    public function getWantedCommodity(): CommodityInterface
    {
        return $this->wantedCommodity;
    }

    #[Override]
    public function setWantedCommodity(CommodityInterface $wantedCommodity): TradeTransactionInterface
    {
        $this->wantedCommodity = $wantedCommodity;

        return $this;
    }

    #[Override]
    public function getOfferedCommodity(): CommodityInterface
    {
        return $this->offeredCommodity;
    }

    #[Override]
    public function setOfferedCommodity(CommodityInterface $offeredCommodity): TradeTransactionInterface
    {
        $this->offeredCommodity = $offeredCommodity;

        return $this;
    }
}
