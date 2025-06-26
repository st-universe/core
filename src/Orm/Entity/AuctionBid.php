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
use Stu\Orm\Repository\AuctionBidRepository;

#[Table(name: 'stu_auction_bid')]
#[Index(name: 'auction_bid_sort_idx', columns: ['max_amount'])]
#[Entity(repositoryClass: AuctionBidRepository::class)]
class AuctionBid
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $auction_id;

    #[Column(type: 'integer')]
    private int $user_id;

    #[Column(type: 'integer')]
    private int $max_amount;

    #[ManyToOne(targetEntity: Deals::class)]
    #[JoinColumn(name: 'auction_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Deals $auction;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuctionId(): int
    {
        return $this->auction_id;
    }


    public function setAuctionId(int $auction_id): AuctionBid
    {
        $this->auction_id = $auction_id;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }


    public function setUserId(int $user_id): AuctionBid
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getMaxAmount(): int
    {
        return $this->max_amount;
    }

    public function setMaxAmount(int $max_amount): AuctionBid
    {
        $this->max_amount = $max_amount;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): AuctionBid
    {
        $this->user = $user;

        return $this;
    }

    public function getAuction(): Deals
    {
        return $this->auction;
    }

    public function setAuction(Deals $auction): AuctionBid
    {
        $this->auction = $auction;

        return $this;
    }
}
