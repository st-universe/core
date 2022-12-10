<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\AuctionBidRepository")
 * @Table(
 *     name="stu_auction_bid",
 *     indexes={
 *         @Index(name="auction_bid_sort_idx", columns={"max_amount"})
 *     }
 * )
 **/
class AuctionBid implements AuctionBidInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer")  */
    private $auction_id;

    /** @Column(type="integer")  */
    private $user_id;

    /** @Column(type="integer", nullable=true)  */
    private $actual_amount;

    /** @Column(type="integer", nullable=true)  */
    private $max_amount;


    /**
     * @ManyToOne(targetEntity="Deals")
     * @JoinColumn(name="auction_id", referencedColumnName="id")
     */
    private $auction;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuctionId(): int
    {
        return $this->auction_id;
    }


    public function setAuctionId(int $auction_id): AuctionBidInterface
    {
        $this->auction_id = $auction_id;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }


    public function setUserId(int $user_id): AuctionBidInterface
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getMaxAmount(): int
    {
        return $this->max_amount;
    }

    public function setMaxAmount(int $max_amount): AuctionBidInterface
    {
        $this->max_amount = $max_amount;

        return $this;
    }

    public function getUser(): UserInterface
    {

        return $this->user;
    }

    public function setUser(UserInterface $user): AuctionBidInterface
    {
        $this->user = $user;

        return $this;
    }

    public function getAuction(): DealsInterface
    {

        return $this->auction;
    }

    public function setAuction(DealsInterface $auction): AuctionBidInterface
    {
        $this->auction = $auction;

        return $this;
    }
}
