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
use Stu\Orm\Repository\TradeTransactionRepository;

#[Table(name: 'stu_trade_transaction')]
#[Index(name: 'trade_transaction_date_tradepost_idx', columns: ['date', 'tradepost_id'])]
#[Entity(repositoryClass: TradeTransactionRepository::class)]
class TradeTransaction
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
    private Commodity $wantedCommodity;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'gg_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Commodity $offeredCommodity;

    public function getId(): int
    {
        return $this->id;
    }

    public function getWantedCommodityId(): int
    {
        return $this->wg_id;
    }

    public function setWantedCommodityId(int $wantedCommodityId): TradeTransaction
    {
        $this->wg_id = $wantedCommodityId;

        return $this;
    }

    public function getWantedCommodityCount(): int
    {
        return $this->wg_count;
    }

    public function setWantedCommodityCount(int $wantedCommodityCount): TradeTransaction
    {
        $this->wg_count = $wantedCommodityCount;

        return $this;
    }

    public function getOfferedCommodityId(): int
    {
        return $this->gg_id;
    }

    public function setOfferedCommodityId(int $offeredCommodityId): TradeTransaction
    {
        $this->gg_id = $offeredCommodityId;

        return $this;
    }

    public function getOfferedCommodityCount(): int
    {
        return $this->gg_count;
    }

    public function setOfferedCommodityCount(int $offeredCommodityCount): TradeTransaction
    {
        $this->gg_count = $offeredCommodityCount;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): TradeTransaction
    {
        $this->date = $date;

        return $this;
    }

    public function getTradePostId(): int
    {
        return $this->tradepost_id;
    }

    public function setTradePostId(int $tradepost_id): TradeTransaction
    {
        $this->tradepost_id = $tradepost_id;

        return $this;
    }

    public function getWantedCommodity(): Commodity
    {
        return $this->wantedCommodity;
    }

    public function setWantedCommodity(Commodity $wantedCommodity): TradeTransaction
    {
        $this->wantedCommodity = $wantedCommodity;

        return $this;
    }

    public function getOfferedCommodity(): Commodity
    {
        return $this->offeredCommodity;
    }

    public function setOfferedCommodity(Commodity $offeredCommodity): TradeTransaction
    {
        $this->offeredCommodity = $offeredCommodity;

        return $this;
    }
}
