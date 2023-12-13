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

#[Table(name: 'stu_auction_bid')]
#[Index(name: 'auction_bid_sort_idx', columns: ['max_amount'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\AuctionBidRepository')]
class AuctionBid implements AuctionBidInterface
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

    #[ManyToOne(targetEntity: 'Deals')]
    #[JoinColumn(name: 'auction_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private DealsInterface $auction;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

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
