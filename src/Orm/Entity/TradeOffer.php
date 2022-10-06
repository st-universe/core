<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TradeOfferRepository")
 * @Table(
 *     name="stu_trade_offers",
 *     indexes={
 *         @Index(name="trade_post_user_idx", columns={"posts_id","user_id"})
 *     }
 * )
 **/
class TradeOffer implements TradeOfferInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $user_id = 0;

    /** @Column(type="integer") * */
    private $posts_id = 0;

    /** @Column(type="smallint") * */
    private $amount = 0;

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

    /**
     * @ManyToOne(targetEntity="TradePost")
     * @JoinColumn(name="posts_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $tradePost;

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

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @OneToOne(targetEntity="Storage", mappedBy="tradeOffer")
     */
    private $storage;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getTradePostId(): int
    {
        return $this->posts_id;
    }

    public function setTradePostId(int $tradePostId): TradeOfferInterface
    {
        $this->posts_id = $tradePostId;

        return $this;
    }

    public function getOfferCount(): int
    {
        return $this->amount;
    }

    public function setOfferCount(int $offerCount): TradeOfferInterface
    {
        $this->amount = $offerCount;

        return $this;
    }

    public function getWantedGoodId(): int
    {
        return $this->wg_id;
    }

    public function setWantedGoodId(int $wantedCommodityId): TradeOfferInterface
    {
        $this->wg_id = $wantedCommodityId;

        return $this;
    }

    public function getWantedGoodCount(): int
    {
        return $this->wg_count;
    }

    public function setWantedGoodCount(int $wantedGoodCount): TradeOfferInterface
    {
        $this->wg_count = $wantedGoodCount;

        return $this;
    }

    public function getOfferedGoodId(): int
    {
        return $this->gg_id;
    }

    public function setOfferedGoodId(int $offeredCommodityId): TradeOfferInterface
    {
        $this->gg_id = $offeredCommodityId;

        return $this;
    }

    public function getOfferedGoodCount(): int
    {
        return $this->gg_count;
    }

    public function setOfferedGoodCount(int $offeredGoodCount): TradeOfferInterface
    {
        $this->gg_count = $offeredGoodCount;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): TradeOfferInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getTradePost(): TradePostInterface
    {
        return $this->tradePost;
    }

    public function setTradePost(TradePostInterface $tradePost): TradeOfferInterface
    {
        $this->tradePost = $tradePost;

        return $this;
    }

    public function getWantedCommodity(): CommodityInterface
    {
        return $this->wantedCommodity;
    }

    public function setWantedCommodity(CommodityInterface $wantedCommodity): TradeOfferInterface
    {
        $this->wantedCommodity = $wantedCommodity;

        return $this;
    }

    public function getOfferedCommodity(): CommodityInterface
    {
        return $this->offeredCommodity;
    }

    public function setOfferedCommodity(CommodityInterface $offeredCommodity): TradeOfferInterface
    {
        $this->offeredCommodity = $offeredCommodity;

        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): TradeOfferInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }
}
