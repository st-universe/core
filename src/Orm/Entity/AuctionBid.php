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
use Stu\Orm\Repository\AuctionBidRepository;

#[Table(name: 'stu_auction_bid')]
#[Index(name: 'auction_bid_sort_idx', columns: ['max_amount'])]
#[Entity(repositoryClass: AuctionBidRepository::class)]
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

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getAuctionId(): int
    {
        return $this->auction_id;
    }


    #[Override]
    public function setAuctionId(int $auction_id): AuctionBidInterface
    {
        $this->auction_id = $auction_id;

        return $this;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }


    #[Override]
    public function setUserId(int $user_id): AuctionBidInterface
    {
        $this->user_id = $user_id;

        return $this;
    }

    #[Override]
    public function getMaxAmount(): int
    {
        return $this->max_amount;
    }

    #[Override]
    public function setMaxAmount(int $max_amount): AuctionBidInterface
    {
        $this->max_amount = $max_amount;

        return $this;
    }

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): AuctionBidInterface
    {
        $this->user = $user;

        return $this;
    }

    #[Override]
    public function getAuction(): DealsInterface
    {
        return $this->auction;
    }

    #[Override]
    public function setAuction(DealsInterface $auction): AuctionBidInterface
    {
        $this->auction = $auction;

        return $this;
    }
}
