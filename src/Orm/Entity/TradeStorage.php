<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TradeStorageRepository")
 * @Table(
 *     name="stu_trade_storage",
 *     indexes={
 *         @Index(name="user_idx", columns={"user_id"})
 *     }
 * )
 **/
class TradeStorage implements TradeStorageInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $user_id = 0;

    /** @Column(type="integer") * */
    private $posts_id = 0;

    /** @Column(type="integer") * */
    private $goods_id = 0;

    /** @Column(type="integer") * */
    private $count = 0;

    /**
     * @ManyToOne(targetEntity="TradePost")
     * @JoinColumn(name="posts_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $tradePost;

    /**
     * @ManyToOne(targetEntity="Commodity")
     * @JoinColumn(name="goods_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $commodity;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): TradeStorageInterface
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getTradePostId(): int
    {
        return $this->posts_id;
    }

    public function setTradePostId(int $tradePostId): TradeStorageInterface
    {
        $this->posts_id = $tradePostId;

        return $this;
    }

    public function getGoodId(): int
    {
        return $this->goods_id;
    }

    public function setGoodId(int $commodityId): TradeStorageInterface
    {
        $this->goods_id = $commodityId;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->count;
    }

    public function setAmount(int $amount): TradeStorageInterface
    {
        $this->count = $amount;

        return $this;
    }

    public function getTradePost(): TradePostInterface
    {
        return $this->tradePost;
    }

    public function setTradePost(TradePostInterface $tradePost): TradeStorageInterface
    {
        $this->tradePost = $tradePost;

        return $this;
    }

    public function getGood(): CommodityInterface
    {
        return $this->commodity;
    }

    public function setGood(CommodityInterface $commodity): TradeStorageInterface
    {
        $this->commodity = $commodity;

        return $this;
    }
}
