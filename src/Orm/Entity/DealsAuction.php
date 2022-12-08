<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\DealsAuctionRepository")
 * @Table(
 *     name="stu_deals_auction",
 *     indexes={
 *         @Index(name="deals_auction_idx", columns={"id"})
 *     }
 * )
 **/
class DealsAuction implements DealsAuctionInterface
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
    private $userid;

    /** @Column(type="integer")  */
    private $actual_amount;

    /** @Column(type="integer", nullable=true)  */
    private $max_amount;


    /**
     * @ManyToOne(targetEntity="Deals")
     * @JoinColumn(name="auction_id", referencedColumnName="id")
     */
    private $auctionid;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="userid", referencedColumnName="id")
     */
    private $auctionuser;

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuctionId(): ?int
    {
        return $this->auction_id;
    }


    public function setAuctionId(int $auction_id): DealsAuctionInterface
    {
        $this->auction_id = $auction_id;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }


    public function setUserId(int $user_id): DealsAuctionInterface
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getActualAmount(): ?int
    {
        return $this->actual_amount;
    }


    public function setActualAmount(int $actual_amount): DealsAuctionInterface
    {
        $this->actual_amount = $actual_amount;

        return $this;
    }

    public function getMaxAmount(): ?int
    {
        return $this->max_amount;
    }


    public function setMaxAmount(int $max_amount): DealsAuctionInterface
    {
        $this->max_amount = $max_amount;

        return $this;
    }


    public function getAuctionUser(): ?UserInterface
    {
        $this->auctionuser;

        return $this;
    }

    public function setAuctionUser(UserInterface $auctionuser): DealsAuctionInterface
    {
        $this->auctionuser = $auctionuser;

        return $this;
    }

    public function getAuction(): ?DealsInterface
    {
        $this->auctionid;

        return $this;
    }

    public function setAuction(DealsInterface $auctionid): DealsAuctionInterface
    {
        $this->auctionid = $auctionid;

        return $this;
    }
}