<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TradeLicenseRepository")
 * @Table(
 *     name="stu_trade_license",
 *     indexes={
 *         @Index(name="user_trade_post_idx", columns={"user_id","posts_id"})
 *     }
 * )
 **/
class TradeLicense implements TradeLicenseInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $posts_id = 0;

    /** @Column(type="integer") * */
    private $user_id = 0;

    /** @Column(type="integer") * */
    private $date = 0;

    /** @Column(type="integer") * */
    private $expired = 0;

    /**
     * @ManyToOne(targetEntity="TradePost")
     * @JoinColumn(name="posts_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $tradePost;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTradePostId(): int
    {
        return $this->posts_id;
    }

    public function setTradePostId(int $tradePostId): TradeLicenseInterface
    {
        $this->posts_id = $tradePostId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): TradeLicenseInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getExpired(): int
    {
        return $this->expired;
    }

    public function setExpired(int $expired): TradeLicenseInterface
    {
        $this->expired = $expired;

        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): TradeLicenseInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getTradePost(): TradePostInterface
    {
        return $this->tradePost;
    }

    public function setTradePost(TradePostInterface $tradePost): TradeLicenseInterface
    {
        $this->tradePost = $tradePost;

        return $this;
    }
}
