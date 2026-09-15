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
use Stu\Orm\Repository\OrionAuctionBidRepository;

#[Table(name: 'stu_orion_auction_bid')]
#[Index(name: 'orion_auction_bid_auction_idx', columns: ['auction_id', 'max_amount'])]
#[Entity(repositoryClass: OrionAuctionBidRepository::class)]
class OrionAuctionBid
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $max_amount;

    #[ManyToOne(targetEntity: OrionAuction::class)]
    #[JoinColumn(name: 'auction_id', referencedColumnName: 'id', nullable: false)]
    private OrionAuction $auction;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getMaxAmount(): int
    {
        return $this->max_amount;
    }

    public function setMaxAmount(int $maxAmount): OrionAuctionBid
    {
        $this->max_amount = $maxAmount;

        return $this;
    }

    public function getAuction(): OrionAuction
    {
        return $this->auction;
    }

    public function setAuction(OrionAuction $auction): OrionAuctionBid
    {
        $this->auction = $auction;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): OrionAuctionBid
    {
        $this->user = $user;

        return $this;
    }
}
