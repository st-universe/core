<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TradeTransactionRepository")
 * @Table(
 *     name="stu_trade_transaction",
 *     indexes={
 *         @Index(name="trade_transaction_date_tradepost_idx", columns={"date", "tradepost_id"})
 *     }
 * )
 **/
class TradeTransaction implements TradeTransactionInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $wg_id = 0;

    /** @Column(type="integer") * */
    private $wg_count = 0;

    /** @Column(type="integer") * */
    private $gg_id = 0;

    /** @Column(type="integer") * */
    private $gg_count = 0;

    /** @Column(type="integer") * */
    private $date = 0;

    /** @Column(type="integer", nullable=true) * */
    private $tradepost_id = 0;

    /**
     * @ManyToOne(targetEntity="Commodity")
     * @JoinColumn(name="wg_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $wantedCommodity;

    /**
     * @ManyToOne(targetEntity="Commodity")
     * @JoinColumn(name="gg_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $offeredCommodity;

    public function getId(): int
    {
        return $this->id;
    }

    public function getWantedGoodId(): int
    {
        return $this->wg_id;
    }

    public function setWantedGoodId(int $wantedCommodityId): TradeTransactionInterface
    {
        $this->wg_id = $wantedCommodityId;

        return $this;
    }

    public function getWantedGoodCount(): int
    {
        return $this->wg_count;
    }

    public function setWantedGoodCount(int $wantedGoodCount): TradeTransactionInterface
    {
        $this->wg_count = $wantedGoodCount;

        return $this;
    }

    public function getOfferedGoodId(): int
    {
        return $this->gg_id;
    }

    public function setOfferedGoodId(int $offeredCommodityId): TradeTransactionInterface
    {
        $this->gg_id = $offeredCommodityId;

        return $this;
    }

    public function getOfferedGoodCount(): int
    {
        return $this->gg_count;
    }

    public function setOfferedGoodCount(int $offeredGoodCount): TradeTransactionInterface
    {
        $this->gg_count = $offeredGoodCount;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): TradeTransactionInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getTradePostId(): int
    {
        return $this->tradepost_id;
    }

    public function setTradePostId(int $tradepost_id): TradeTransactionInterface
    {
        $this->tradepost_id = $tradepost_id;

        return $this;
    }

    public function getWantedCommodity(): CommodityInterface
    {
        return $this->wantedCommodity;
    }

    public function setWantedCommodity(CommodityInterface $wantedCommodity): TradeTransactionInterface
    {
        $this->wantedCommodity = $wantedCommodity;

        return $this;
    }

    public function getOfferedCommodity(): CommodityInterface
    {
        return $this->offeredCommodity;
    }

    public function setOfferedCommodity(CommodityInterface $offeredCommodity): TradeTransactionInterface
    {
        $this->offeredCommodity = $offeredCommodity;

        return $this;
    }
}
